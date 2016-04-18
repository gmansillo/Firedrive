<?php

/**
 * @version     1.0.0
 * @package     com_simplefilemanager
 * @copyright   
 * @license     
 * @author       <> - 
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class SimplefilemanagerViewSimplefilemanager extends JViewLegacy {

    protected $state;
    protected $item;
    protected $form;
    protected $params;

    /**
     * Display the view
     */
    public function display($tpl = null) {

    	$this->doc 		= JFactory::getDocument();
    	$this->app		= JFactory::getApplication();
    	$this->params 	= $this->app->getParams();
    	$this->menu		= $this->app->getMenu()->getActive();
    	//$this->subview = $this->menu->params->get('subview');
    	$this->subview = 'table';
    	$this->defIcon 	= $this->params->get('defaulticon',"./media/com_simplefilemanager/images/download.gif");

        $this->showDate		= $this->app->input->get('showDate', $this->params->get('showDate',1,"int"));
    	$this->showIcon		= $this->app->input->get('showIcon', $this->params->get('showIcon',1,"int"));
    	$this->showDesc		= $this->app->input->get('showDesc', $this->params->get('showDesc',1,"int"));
    	$this->showAuth 	= $this->app->input->get('showAuth', $this->params->get('showAuth',1,"int"));
    	$this->showLicence	= $this->app->input->get('showLicence', $this->params->get('showLicence',1,"int"));
    	$this->showSize		= $this->app->input->get('showSize', $this->params->get('showSize',1,"int"));
    	$this->showMD5		= $this->app->input->get('showMD5', $this->params->get('showMD5',1,"int"));
    	$this->showNew		= $this->app->input->get('showNew', $this->params->get('showNew',1,"int"));
    	$this->newfiledays 	= $this->params->get('newfiledays',7,"int");
    	$this->show_page_heading =  $this->app->input->get('show_page_heading',1,"int");
    	 
    	
        $app = JFactory::getApplication();
        $user = JFactory::getUser();

        $this->state = $this->get('State');
        $this->item = $this->get('Data');
        $this->params = $app->getParams('com_simplefilemanager');

        if (!empty($this->item)) {
		    $this->form		= $this->get('Form');
        }

        $this->item->icon = $this->item->icon?:$this->defIcon;
        $this->item->canDownload    = (
            ($this->item->visibility == 1)
            || ($this->item->visibility == 3 && $this->item->reserved_user == $this->user->id)
            || ($this->item->visibility == 2 && $user->id)
            || ($this->item->visibility == 5 && $this->author == $user->id)
            || ($this->item->visibility == 4 && in_array($this->item->reserved_group, JAccess::getGroupsByUser($user->id)))
        );

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        

        if ($this->_layout == 'edit') {

            $authorised = $user->authorise('core.create', 'com_simplefilemanager');

            if ($authorised !== true) {
                throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
            }
        }

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document
     */
    protected function _prepareDocument() {
        $app = JFactory::getApplication();
        $menus = $app->getMenu();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', $this->item->title);
        }
        $title = $this->params->get('page_title', '');
        if (empty($title)) {
            $title = $app->getCfg('sitename');
        } elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        } elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
        }
        $this->document->setTitle($title);

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }

}
