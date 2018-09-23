<?php

/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @copyright   Firedrive
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Document table
 * @since 5.2.1
 */
class FiredriveTableDocument extends JTable
{

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver &$db Database connector object
	 *
	 * @since 5.2.1
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__firedrive', 'id', $db);

		// TODO: Check how to fix this in Joomla 4.0 (JTableObserverContenthistory is deprecated)
		JTableObserverContenthistory::createObserver($this, array('typeAlias' => 'com_firedrive.document'));

		$this->created = JFactory::getDate()->toSql();
		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Increase download counter
	 *
	 * @return  void
	 * @since 5.2.1
	 */
	public function download_counter()
	{
		$query = 'UPDATE #__firedrive'
			. ' SET download_counter = (download_counter + 1)'
			. ' WHERE id = ' . (int) $this->id;

		$this->_db->setQuery($query);
		$this->_db->execute();
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean
	 * @see     JTable::check
	 * @since   5.2.1
	 */
	public function check()
	{
		// Set title
		$this->title = htmlspecialchars_decode($this->title, ENT_QUOTES);

		// Set alias
		if (trim($this->alias) == '')
		{
			$this->alias = $this->title;
		}

		$this->alias = JApplicationHelper::stringURLSafe($this->alias, $this->language);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		// Check the publish down date is not earlier than publish up.
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up)
		{
			$this->setError(JText::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));

			return false;
		}

		// Set ordering
		if ($this->state < 0)
		{
			// Set ordering to 0 if state is archived or trashed
			$this->ordering = 0;
		}
		elseif (empty($this->ordering))
		{
			// Set ordering to last if ordering was 0
			$this->ordering = self::getNextOrder($this->_db->quoteName('catid') . '=' . $this->_db->quote($this->catid) . ' AND state>=0');
		}

		if (empty($this->publish_up))
		{
			$this->publish_up = $this->getDbo()->getNullDate();
		}

		if (empty($this->publish_down))
		{
			$this->publish_down = $this->getDbo()->getNullDate();
		}

		if (empty($this->modified))
		{
			$this->modified = $this->getDbo()->getNullDate();
		}

		return true;
	}

	/**
	 * Overloaded bind function
	 *
	 * @param   mixed $array  An associative array or object to bind to the JTable instance.
	 * @param   mixed $ignore An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success
	 * @since 5.2.1
	 */
	public function bind($array, $ignore = array())
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry($array['params']);

			if ((int) $registry->get('width', 0) < 0)
			{
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_NEGATIVE_NOT_PERMITTED', JText::_('COM_FIREDRIVE_FIELD_WIDTH_LABEL')));

				return false;
			}

			if ((int) $registry->get('height', 0) < 0)
			{
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_NEGATIVE_NOT_PERMITTED', JText::_('COM_FIREDRIVE_FIELD_HEIGHT_LABEL')));

				return false;
			}

			// Converts the width and height to an absolute numeric value:
			$width  = abs((int) $registry->get('width', 0));
			$height = abs((int) $registry->get('height', 0));

			// Sets the width and height to an empty string if = 0
			$registry->set('width', $width ?: '');
			$registry->set('height', $height ?: '');

			$array['params'] = (string) $registry;
		}

		if (isset($array['imptotal']))
		{
			$array['imptotal'] = abs((int) $array['imptotal']);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to store a row
	 *
	 * @param   boolean $updateNulls True to update fields even if they are null.
	 *
	 * @return  boolean  True on success, false on failure.
	 * @since 5.2.1
	 */
	public function store($updateNulls = false)
	{
		if (empty($this->id))
		{
			// Store the row
			parent::store($updateNulls);
		}
		else
		{
			// Get the old row
			/** @var FiredriveTableDocument $oldrow */
			$oldrow = JTable::getInstance('Document', 'FiredriveTable');

			if (!$oldrow->load($this->id) && $oldrow->getError())
			{
				$this->setError($oldrow->getError());
			}

			// Verify that the alias is unique
			/** @var FiredriveTableDocument $table */
			$table = JTable::getInstance('Document', 'FiredriveTable');

			if ($table->load(array('alias' => $this->alias, 'catid' => $this->catid)) && ($table->id != $this->id || $this->id == 0))
			{
				$this->setError(JText::_('COM_FIREDRIVE_ERROR_UNIQUE_ALIAS'));

				return false;
			}

			// Store the new row
			parent::store($updateNulls);

			// Need to reorder ?
			if ($oldrow->state >= 0 && ($this->state < 0 || $oldrow->catid != $this->catid))
			{
				// Reorder the oldrow
				$this->reorder($this->_db->quoteName('catid') . '=' . $this->_db->quote($oldrow->catid) . ' AND state>=0');
			}
		}

		return count($this->getErrors()) == 0;
	}

}
