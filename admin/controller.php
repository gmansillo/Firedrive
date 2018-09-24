<?php

/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @copyright   Firedrive
 */
defined('_JEXEC') or die;

/**
 * Documents master display controller.
 * @since   5.2.1
 */
class FiredriveController extends JControllerLegacy
{

	protected $default_view = 'documents';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean $cachable  If true, the view output will be cached
	 * @param   array   $urlparams An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  FiredriveController  This object to support chaining.
	 * @since   5.2.1
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$view   = $this->input->get('view', 'documents');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($view == 'document' && $layout == 'edit' && !$this->checkEditId('com_firedrive.edit.document', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_firedrive&view=documents', false));

			return false;
		}

		return parent::display();
	}

	/**
	 * Method to download a specified document.
	 *
	 * @return  void
	 * @since   5.2.1
	 */
	public function download()
	{
		$jinput = JFactory::getApplication()->input;
		$id     = $jinput->get->get('id', 0, 'INTEGER');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('file_name');
		$query->from($db->quoteName('#__firedrive'));
		$query->where($db->quoteName('id') . ' = ' . $id);
		$db->setQuery($query);
		$row = $db->loadRow();

		if (!$row || !file_exists($row['0']))
		{
			throw new Exception(JText::_("COM_FIREDRIVE_FILE_NOT_FOUND_DOWNLOAD_ERROR_MESSAGE"), 404);
		}

		header("Content-Transfer-Encoding: binary");
		header("Content-type: application/octet-stream");
		header('Content-Disposition: attachment; filename="' . basename($row['0']) . '"');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Cache-Control: private');
		header('Content-Length: ' . filesize($row['0']));
		ob_clean();
		flush();
		readfile($row['0']);
		exit;
	}

}
