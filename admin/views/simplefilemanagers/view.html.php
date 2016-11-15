<?php
/**
 *
 * @package     Simple File Manager
 * @author        Giovanni Mansillo
 *
 * @copyright   Copyright (C) 2005 - 2014 Giovanni Mansillo. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');

class SimplefilemanagerViewSimplefilemanagers extends JViewLegacy
{
    protected $items;

    protected $state;

    protected $pagination;

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->state      = $this->get('State');
        $this->pagination = $this->get('Pagination');

        SimplefilemanagerHelper::addSubmenu('simplefilemanagers');

        if (count($errors = $this->get('Errors')))
        {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();

        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        $state = $this->get('State');

        $canDo = SimplefilemanagerHelper::getActions($state->get('filter.category_id'));

        $user = JFactory::getUser();
        $bar  = JToolBar::getInstance('toolbar');

        JToolbarHelper::title(JText::_('COM_SIMPLEFILEMANAGER_MANAGER_SIMPLEFILEMANAGERS'), 'file-2');

        // User should be authorized to publish at least in a category
        if ($canDo->get('core.create') && count($user->getAuthorisedCategories('com_simplefilemanager', 'core.create')) > 0)
        {
            JToolbarHelper::addNew('simplefilemanager.add');
        }

        if ($canDo->get('core.edit'))
        {
            JToolbarHelper::editList('simplefilemanager.edit');
        }
        if ($canDo->get('core.edit.state'))
        {

            JToolbarHelper::publish('simplefilemanagers.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('simplefilemanagers.unpublish', 'JTOOLBAR_UNPUBLISH', true);

            JToolbarHelper::archiveList('simplefilemanagers.archive');
            JToolbarHelper::checkin('simplefilemanagers.checkin');
        }

        $state = $this->get('State');

        if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
        {
            JToolbarHelper::deleteList('', 'simplefilemanagers.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
        elseif ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::trash('simplefilemanagers.trash');
        }

        if ($canDo->get('core.admin'))
        {
            JToolbarHelper::preferences('com_simplefilemanager');
        }

        JToolBarHelper::help('COM_SIMPLEFILEMANAGER_HELP_VIEW_SIMPLEFILEMANANGERS', false, 'http://www.simplefilemanager.eu/support');
        JHtmlSidebar::setAction('index.php?option=com_simplefilemanager&view=simplefilemanagers');

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_PUBLISHED'),
            'filter_state',
            JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true)
        );

        $this->category        = JFormHelper::loadFieldType('catid', false);
        $this->categoryOptions = $this->category->getOptions();

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_CATEGORY'),
            'filter_category',
            JHtml::_('select.options', $this->categoryOptions, 'value', 'text', $this->state->get('filter.category'))
        );

        $this->visibilityOptions = array(
            1 => JText::_('COM_SIMPLEFILEMANAGER_VISIBILITY_PUBLIC'),
            2 => JText::_('COM_SIMPLEFILEMANAGER_VISIBILITY_REGISTRED'),
            3 => JText::_('COM_SIMPLEFILEMANAGER_VISIBILITY_USER'),
            4 => JText::_('COM_SIMPLEFILEMANAGER_VISIBILITY_GROUP'),
            5 => JText::_('COM_SIMPLEFILEMANAGER_VISIBILITY_AUTHOR')
        );

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_VISIBILITY'),
            'filter_visibility',
            JHtml::_('select.options', $this->visibilityOptions, 'value', 'text', $this->state->get('filter.visibility'))
        );
    }

    protected function getSortFields()
    {
        return array(
            'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
            'a.visibility' => JText::_('COM_SIMPLEFILEMANAGER_FIELD_VISIBILITY_LABEL'),
            'a.file_created' => JText::_('COM_SIMPLEFILEMANAGER_HEADING_CREATION'),
            'a.author' => JText::_('JAUTHOR'),
            'a.download_counter' => JText::_('COM_SIMPLEFILEMANAGER_HEADING_HITS'),
            'a.state' => JText::_('JSTATUS'),
            'a.title' => JText::_('JGLOBAL_TITLE'),
            'a.id' => JText::_('JGRID_HEADING_ID')
        );
    }
}