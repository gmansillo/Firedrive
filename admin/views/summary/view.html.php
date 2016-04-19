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

class SimplefilemanagerViewSummary extends JViewLegacy
{

    public function display($tpl = null)
    {

        SimplefilemanagerHelper::addSubmenu('summary');

        if (count($errors = $this->get('Errors')))
        {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JHtmlSidebar::setAction('index.php?option=com_simplefilemanager&view=summary');
        $bar = JToolBar::getInstance('toolbar');
        JToolbarHelper::title(JText::_('COM_SIMPLEFILEMANAGER_MANAGER_SUMMARY'), 'chart');
        JToolbarHelper::back();
        //JToolBarHelper::custom('reload', 'loop', 'loop','', false);

    }

}