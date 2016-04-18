<?php

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class SimplefilemanagerController extends JControllerLegacy {

    public function display($cachable = false, $urlparams = false) {
        require_once JPATH_COMPONENT . '/helpers/simplefilemanager.php';

        $view = JFactory::getApplication()->input->getCmd('view', 'simplefilemanagers');
        JFactory::getApplication()->input->set('view', $view);

        parent::display($cachable, $urlparams);

        return $this;
    }
    
   
}
