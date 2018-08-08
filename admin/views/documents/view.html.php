<?php

/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;

/**
 * View class for a list of documents.
 */
class SimplefilemanagerViewDocuments extends JViewLegacy {

    /**
     * Category data
     *
     * @var  array
     */
    protected $categories;

    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var  JPagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var  object
     */
    protected $state;

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  A template file to load. [optional]
     *
     * @return  mixed  A string if successful, otherwise a JError object.
     */
    public function display($tpl = null) {

        $this->categories = $this->get('CategoryOrders');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
        }

        SimplefilemanagerHelper::addSubmenu('documents');

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
     */
    protected function addToolbar() {
        JLoader::register('SimplefilemanagerHelper', JPATH_ADMINISTRATOR . '/components/com_simplefilemanager/helpers/simplefilemanager.php');

        $canDo = JHelperContent::getActions('com_simplefilemanager', 'category', $this->state->get('filter.category_id'));
        $user = JFactory::getUser();

        JToolbarHelper::title(JText::_('COM_SIMPLEFILEMANAGER_MANAGER_DOCUMENTS'), 'list documents');

        if (count($user->getAuthorisedCategories('com_simplefilemanager', 'core.create')) > 0) {
            JToolbarHelper::addNew('document.add');
        }

        if ($canDo->get('core.edit')) {
            JToolbarHelper::editList('document.edit');
        }

        if ($canDo->get('core.edit.state')) {
            if ($this->state->get('filter.published') != 2) {
                JToolbarHelper::publish('documents.publish', 'JTOOLBAR_PUBLISH', true);
                JToolbarHelper::unpublish('documents.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            }

            if ($this->state->get('filter.published') != -1) {
                if ($this->state->get('filter.published') != 2) {
                    JToolbarHelper::archiveList('documents.archive');
                } elseif ($this->state->get('filter.published') == 2) {
                    JToolbarHelper::unarchiveList('documents.publish');
                }
            }
        }

        if ($canDo->get('core.edit.state')) {
            JToolbarHelper::checkin('documents.checkin');
        }

        // Add a batch button
        if ($user->authorise('core.create', 'com_simplefilemanager') && $user->authorise('core.edit', 'com_simplefilemanager') && $user->authorise('core.edit.state', 'com_simplefilemanager')) {
            $title = JText::_('JTOOLBAR_BATCH');

            // Instantiate a new JLayoutFile instance and render the batch button
            $layout = new JLayoutFile('joomla.toolbar.batch');

            $dhtml = $layout->render(array('title' => $title));
            JToolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
        }

        JToolbar::getInstance('toolbar')->appendButton('Link', 'box-remove', 'Import', 'index.php?option=com_simplefilemanager&view=import');

        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'documents.delete', 'JTOOLBAR_EMPTY_TRASH');
        } elseif ($canDo->get('core.edit.state')) {
            JToolbarHelper::trash('documents.trash');
        }

        if ($user->authorise('core.admin', 'com_simplefilemanager') || $user->authorise('core.options', 'com_simplefilemanager')) {
            JToolbarHelper::preferences('com_simplefilemanager');
        }
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields() {
        return array(
            'ordering' => JText::_('JGRID_HEADING_ORDERING'),
            'a.state' => JText::_('JSTATUS'),
            'a.title' => JText::_('COM_SIMPLEFILEMANAGER_HEADING_TITLE'),
            'a.download_counter' => JText::_('COM_SIMPLEFILEMANAGER_HEADING_DOWNLOAD_COUNTER'),
            'a.language' => JText::_('JGRID_HEADING_LANGUAGE'),
            'a.id' => JText::_('JGRID_HEADING_ID'),
        );
    }

}
