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

class SimplefilemanagerViewSimplefilemanager extends JViewLegacy
{
    protected $item;

    protected $form;

    public function display($tpl = null)
    {
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        if (count($errors = $this->get('Errors')))
        {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        $this->addToolbar();
        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        $input = JFactory::getApplication()->input;
        $input->set('hidemainmenu', true);

        $user   = JFactory::getUser();
        $userId = $user->get('id');
        $isNew  = ($this->item->id == 0);
        $canDo  = SimplefilemanagerHelper::getActions($this->item->catid, 0);

        if ($isNew)
        {
            JToolBarHelper::title(JText::_('COM_simplefilemanager_new_simplefilemanager'));
        }
        else
        {
            JToolBarHelper::title(JText::_('COM_simplefilemanager_edit_simplefilemanager'));
        }

        if ($canDo->get('core.edit') || (count($user->getAuthorisedCategories('com_simplefilemanager', 'core.create'))))
        {
            JToolbarHelper::apply('simplefilemanager.apply');
            JToolbarHelper::save('simplefilemanager.save');
            if(!$isNew)
            {
                JToolbarHelper::save2new('simplefilemanager.save2new');
                JToolbarHelper::save2copy('simplefilemanager.save2copy');
            }
        }

        JToolBarHelper::cancel('simplefilemanager.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

    }
}