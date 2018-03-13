<?php
/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */

defined('_JEXEC') or die;

/**
 * HTML Document View class for the Simplefilemanager component
 */
class SimplefilemanagerViewDocument extends JViewLegacy
{
	/**
	 * The item model state
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  1.6
	 */
	protected $state;

	/**
	 * The form object for the document item
	 *
	 * @var    JForm
	 * @since  1.6
	 */
	protected $form;

	/**
	 * The item object details
	 *
	 * @var    JObject
	 * @since  1.6
	 */
	protected $item;

	/**
	 * The page to return to on sumission
	 *
	 * @var         string
	 * @since       1.6
	 * @deprecated  4.0  Variable not used
	 */
	protected $return_page;

	/**
	 * Should we show a captcha form for the submission of the document request?
	 *
	 * @var   bool
	 * @since 3.6.3
	 */
	protected $captchaEnabled = false;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		// TODO: Add check on document visibility in document controller for avoiding direct access to the file to unauthorized users

		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		$item = $this->get('Item');
		$state = $this->get('State');

		if (empty($item->catid))
		{
			$app->setUserState('com_simplefilemanager.document.data', array('catid' => $item->catid));
		}

		$this->form = $this->get('Form');

		$params = $state->get('params');

		$temp = clone $params;

		$active = $app->getMenu()->getActive();

		if ($active)
		{
			// If the current view is the active item and a document view for this document, then the menu item params take priority
			if (strpos($active->link, 'view=document') && strpos($active->link, '&id=' . (int) $item->id))
			{
				// $item->params are the document params, $temp are the menu item params
				// Merge so that the menu item params take priority
				$item->params->merge($temp);
			}
			else
			{
				// Current view is not a single document, so the document params take priority here
				// Merge the menu item params with the document params so that the document params take priority
				$temp->merge($item->params);
				$item->params = $temp;
			}
		}
		else
		{
			// Merge so that document params take priority
			$temp->merge($item->params);
			$item->params = $temp;
		}

		if ($item)
		{
			// Get Category Model data
			$categoryModel = JModelLegacy::getInstance('Category', 'SimplefilemanagerModel', array('ignore_request' => true));

			$categoryModel->setState('category.id', $item->catid);
			$categoryModel->setState('list.ordering', 'a.title');
			$categoryModel->setState('list.direction', 'asc');
			$categoryModel->setState('filter.published', 1);

			$docs = $categoryModel->getItems();
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}

		// Check if access is not public
		$groups = $user->getAuthorisedViewLevels();

		$return = '';

		if ((!in_array($item->access, $groups)) || (!in_array($item->category_access, $groups)))
		{
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
		}

		$options['category_id'] = $item->catid;
		$options['order by']    = 'a.default_con DESC, a.ordering ASC';

		// Process the content plugins
		JPluginHelper::importPlugin('content');

		$dispatcher	= JEventDispatcher::getInstance();

		$offset = $state->get('list.offset');

		// Fix for where some plugins require a text attribute
		$item->text = null;

		if (!empty($item->misc))
		{
			$item->text = $item->misc;
		}

		$dispatcher->trigger('onContentPrepare', array('com_simplefilemanager.document', &$item, &$item->params, $offset));

		// Store the events for later
		$item->event = new stdClass;

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_simplefilemanager.document', &$item, &$item->params, $offset));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_simplefilemanager.document', &$item, &$item->params, $offset));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_simplefilemanager.document', &$item, &$item->params, $offset));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		if (!empty($item->text))
		{
			$item->misc = $item->text;
		}

		$docUser = null;

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($item->params->get('pageclass_sfx'));

		$this->doc     = &$item;
		$this->params      = &$item->params;
		$this->return      = &$return;
		$this->state       = &$state;
		$this->item        = &$item;
		$this->user        = &$user;
		$this->docs    = &$docs;
		$this->docUser = $docUser;

		$item->tags = new JHelperTags;
		$item->tags->getItemTags('com_simplefilemanager.document', $this->item->id);

		// Override the layout only if this is not the active menu item
		// If it is the active menu item, then the view and item id will match
		if ((!$active) || ((strpos($active->link, 'view=document') === false) || (strpos($active->link, '&id=' . (string) $this->item->id) === false)))
		{
			if (($layout = $item->params->get('document_layout')))
			{
				$this->setLayout($layout);
			}
		}
		elseif (isset($active->query['layout']))
		{
			// We need to set the layout in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}
		
		$captchaSet = $item->params->get('captcha', JFactory::getApplication()->get('captcha', '0'));

		foreach (JPluginHelper::getPlugin('captcha') as $plugin)
		{
			if ($captchaSet === $plugin->name)
			{
				$this->captchaEnabled = true;
				break;
			}
		}

		$this->_prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function _prepareDocument()
	{
		$app     = JFactory::getApplication();
		$menus   = $app->getMenu();
		$pathway = $app->getPathway();
		$title   = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_SIMPLEFILEMANAGER_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		$id = (int) @$menu->query['id'];

		// If the menu item does not concern this document
		if ($menu && ($menu->query['option'] !== 'com_simplefilemanager' || $menu->query['view'] !== 'document' || $id != $this->item->id))
		{
			// If this is not a single document menu item, set the page title to the document title
			if ($this->item->title)
			{
				$title = $this->item->title;
			}

			$path = array(array('title' => $this->doc->title, 'link' => ''));
			$category = JCategories::getInstance('Simplefilemanager')->get($this->doc->catid);

			while ($category && ($menu->query['option'] !== 'com_simplefilemanager' || $menu->query['view'] === 'document' || $id != $category->id) && $category->id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => SimplefilemanagerHelperRoute::getCategoryRoute($this->doc->catid));
				$category = $category->getParent();
			}

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		if (empty($title))
		{
			$title = $this->item->title;
		}

		$this->document->setTitle($title);

		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}
		elseif ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		$mdata = $this->item->metadata->toArray();

		foreach ($mdata as $k => $v)
		{
			if ($v)
			{
				$this->document->setMetadata($k, $v);
			}
		}
	}
}
