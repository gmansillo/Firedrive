<?php

/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @copyright   Firedrive
 */
defined('_JEXEC') or die;

/**
 * View class for a list of documents.
 * @since   5.2.1
 */
class FiredriveViewDocuments extends JViewLegacy
{

	/**
	 * Category data
	 *
	 * @var  array
	 * @since   5.2.1
	 */
	protected $categories;

	/**
	 * An array of items
	 *
	 * @var  array
	 * @since   5.2.1
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  JPagination
	 * @since   5.2.1
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 * @since   5.2.1
	 */
	protected $state;

	/**
	 * Method to display the view.
	 *
	 * @param   string $tpl A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 * @since   5.2.1
	 */
	public function display($tpl = null)
	{

		$this->categories    = $this->get('CategoryOrders');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		FiredriveHelper::addSubmenu('documents');

		$this->addToolbar();

		// Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 * @since   5.2.1
	 */
	protected function addToolbar()
	{
		JLoader::register('FiredriveHelper', JPATH_ADMINISTRATOR . '/components/com_firedrive/helpers/firedrive.php');

		$canDo = JHelperContent::getActions('com_firedrive', 'category', $this->state->get('filter.category_id'));
		$user  = JFactory::getUser();

		JToolbarHelper::title(JText::_('COM_FIREDRIVE_MANAGER_DOCUMENTS'), 'list folder-2');

		if (count($user->getAuthorisedCategories('com_firedrive', 'core.create')) > 0)
		{
			JToolbarHelper::addNew('document.add');
		}

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('document.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			if ($this->state->get('filter.published') != 2)
			{
				JToolbarHelper::publish('documents.publish', 'JTOOLBAR_PUBLISH', true);
				JToolbarHelper::unpublish('documents.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			}

			if ($this->state->get('filter.published') != -1)
			{
				if ($this->state->get('filter.published') != 2)
				{
					JToolbarHelper::archiveList('documents.archive');
				}
				elseif ($this->state->get('filter.published') == 2)
				{
					JToolbarHelper::unarchiveList('documents.publish');
				}
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::checkin('documents.checkin');
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_firedrive') && $user->authorise('core.edit', 'com_firedrive') && $user->authorise('core.edit.state', 'com_firedrive'))
		{
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			JToolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
		}

		JToolbar::getInstance('toolbar')->appendButton('Link', 'box-remove', 'Import', 'index.php?option=com_firedrive&view=import');

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'documents.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('documents.trash');
		}

		if ($user->authorise('core.admin', 'com_firedrive') || $user->authorise('core.options', 'com_firedrive'))
		{
			JToolbarHelper::preferences('com_firedrive');
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 * @since   5.2.1
	 */
	protected function getSortFields()
	{
		return array(
			'ordering'           => JText::_('JGRID_HEADING_ORDERING'),
			'a.state'            => JText::_('JSTATUS'),
			'a.title'            => JText::_('COM_FIREDRIVE_HEADING_TITLE'),
			'a.download_counter' => JText::_('COM_FIREDRIVE_HEADING_DOWNLOAD_COUNTER'),
			'a.language'         => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id'               => JText::_('JGRID_HEADING_ID'),
		);
	}

}
