<?php

	/**
	 * @version     1.0.0
	 * @package     com_simplefilemanagers
	 * @copyright   Copyright (C) 2015. Tutti i diritti riservati.
	 * @license     GNU General Public License versione 2 o successiva; vedi LICENSE.txt
	 * @author      Giovanni Mansillo <info@flowsolutions.it> - http://www.flowsolutions.it
	 */
// No direct access
	defined('_JEXEC') or die;

	jimport('joomla.application.component.view');
	jimport('joomla.application.categories');

	/**
	 * View class for a list of Simplefilemanagers.
	 */
	class SimplefilemanagerViewSimplefilemanagers extends JViewLegacy
	{

		protected $items;
		protected $pagination;
		protected $state;
		protected $params;


		public function display($tpl = null)
		{
			$this->doc    = JFactory::getDocument();
			$this->app    = JFactory::getApplication();
			$this->user   = JFactory::getUser();
			$this->params = $this->app->getParams();
			$this->menu   = $this->app->getMenu()->getActive();

			// Params
			$this->showDate          = $this->app->input->get('showDate', $this->params->get('showDate', 1, "int"));
			$this->showIcon          = $this->app->input->get('showIcon', $this->params->get('showIcon', 1, "int"));
			$this->showDesc          = $this->app->input->get('showDesc', $this->params->get('showDesc', 1, "int"));
			$this->showAuth          = $this->app->input->get('showAuth', $this->params->get('showAuth', 1, "int"));
			$this->showLicence       = $this->app->input->get('showLicence', $this->params->get('showLicence', 1, "int"));
			$this->showSize          = $this->app->input->get('showSize', $this->params->get('showSize', 1, "int"));
			$this->showMD5           = $this->app->input->get('showMD5', $this->params->get('showMD5', 1, "int"));
			$this->showNew           = $this->app->input->get('showNew', $this->params->get('showNew', 1, "int"));
			$this->newfiledays       = $this->params->get('newfiledays', 7, "int");
			$this->show_page_heading = $this->app->input->get('show_page_heading', 1, "int");
			$this->subview           = $this->menu->params->get('subview', 'list');
			$this->defIcon           = $this->params->get('defaulticon', "./media/com_simplefilemanager/images/download.gif");
			$this->linkOnEntryTitle  = $this->params->get('linkOnTitle', 1);

			// Permissions
			$this->canCreate  = $this->user->authorise('core.create', 'com_simplefilemanager');
			$this->canEdit    = $this->user->authorise('core.edit', 'com_simplefilemanager');
			$this->canCheckin = $this->user->authorise('core.manage', 'com_simplefilemanager');
			$this->canChange  = $this->user->authorise('core.edit.state', 'com_simplefilemanager');
			$this->canDelete  = $this->user->authorise('core.delete', 'com_simplefilemanager');

			// View data
			$this->state         = $this->get('State');
			$this->items         = $this->get('Items');
			$this->pagination    = $this->get('Pagination');
			$this->params        = $this->app->getParams('com_simplefilemanager');
			$this->catID         = $this->app->input->get('catid', 0);
			$this->category      = JCategories::getInstance('Simplefilemanager')->get($this->catID);
			$this->sortDirection = $this->state->get('list.direction');
			$this->sortColumn    = $this->state->get('list.ordering');

			foreach ($this->items as $item)
			{
				$item->icon        = $item->icon ? : $this->defIcon;
				$item->canDownload = (
					($item->visibility == 1)
					|| ($item->visibility == 3 && $item->reserved_user == $this->user->id)
					|| ($item->visibility == 2 && $item->user->id)
					|| ($item->visibility == 5 && $item->author == $this->user->id)
					|| ($item->visibility == 4 && in_array($item->reserved_group, JAccess::getGroupsByUser($this->user->id)))
				);
			}


			if (!$this->catID or !$this->category)
			{
				JError::raiseError(500);
			}
			// TODO: Check if user can view cateogry else throw a 403 error

			$this->children = $this->category->getChildren();

			// Check for errors.
			if (count($errors = $this->get('Errors')))
			{
				throw new Exception(implode("\n", $errors));
			}

			$this->_prepareDocument();

			parent::display($tpl);

			echo JText::_("COM_SIMPLEFILEMANAGER_CREDITS");
		}

		/**
		 * Prepares the document
		 */
		protected function _prepareDocument()
		{
			$app   = JFactory::getApplication();
			$menus = $app->getMenu();
			$title = null;

			// Because the application sets a default page title,
			// we need to get it from the menu item itself
			$menu = $menus->getActive();
			if ($menu)
			{
				$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
			} else
			{
				$this->params->def('page_heading', JText::_('COM_SIMPLEFILEMANAGERS_DEFAULT_PAGE_TITLE'));
			}

			$title = $this->params->get('page_title', '');
			if (empty($title))
			{
				$title = $app->getCfg('sitename');
			} elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
			{
				$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
			} elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
			{
				$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
			}
			$this->document->setTitle($title);

			// Get category description
			$db = JFactory::getDBO();
			if ($this->catID)
			{
				$db->setQuery("SELECT description FROM #__categories WHERE id = " . $this->catID . " LIMIT 1;");
				$this->catDesc = $db->loadResult();
			}

			if ($this->params->get('menu-meta_description'))
			{
				$this->document->setDescription($this->params->get('menu-meta_description'));
			}

			if ($this->params->get('menu-meta_keywords'))
			{
				$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
			}

			if ($this->params->get('robots'))
			{
				$this->document->setMetadata('robots', $this->params->get('robots'));
			}
		}

	}