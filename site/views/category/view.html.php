<?php
/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Simplefilemanager component
 *
 * @since  1.5
 */
class SimplefilemanagerViewCategory extends JViewCategory
{
	/**
	 * @var    string  The name of the extension for the category
	 * @since  3.2
	 */
	protected  $extension = 'com_simplefilemanager';

	/**
	 * @var    string  Default title to use for page title
	 * @since  3.2
	 */
	protected  $defaultPageTitle = 'COM_SIMPLEFILEMANAGER_DEFAULT_PAGE_TITLE';

	/**
	 * @var    string  The name of the view to link individual items to
	 * @since  3.2
	 */
	protected $viewName = 'document';

	/**
	 * Run the standard Joomla plugins
	 *
	 * @var    bool
	 * @since  3.5
	 */
	protected $runPlugins = true;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		parent::commonCategoryDisplay();

		// Prepare the data.
		// Compute the document slug.
		foreach ($this->items as $item)
		{
			$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
			$temp       = $item->params;
			$item->params = clone $this->params;
			$item->params->merge($temp);
		}

		$this->sortFields    = $this->getSortFields();
		
		return parent::display($tpl);
	}

	/**
	 * Get the list of sort fields
	 *
	 * @return  $fields
	 */
	protected function getSortFields()
    {
        $fields = array(
            'a.ordering' => JText::_('JGLOBAL_ORDERING'),
            'a.title' => JText::_('JGLOBAL_TITLE')
		);
		
		if ($this->params->get('show_description', 1))
		{
			$fields['a.description'] = JText::_('JGLOBAL_DESCRIPTION');
		}

		if ($this->params->get('show_created_by', 1))
		{
			$fields['a.created_by'] = JText::_('COM_SIMPLEFILEMANAGER_CREATED_BY');
		}

		if ($this->params->get('show_file_size', 1))
		{
			$fields['a.file_size'] = JText::_('COM_SIMPLEFILEMANAGER_FILE_SIZE');
		}

        return $fields;
    }

	/**
	 * Prepares the document
	 *
	 * @return  void
	 */
	protected function prepareDocument()
	{
		parent::prepareDocument();

		$menu = $this->menu;
		$id = (int) @$menu->query['id'];

		if ($menu && ($menu->query['option'] != $this->extension || $menu->query['view'] == $this->viewName || $id != $this->category->id))
		{
			$path = array(array('title' => $this->category->title, 'link' => ''));
			$category = $this->category->getParent();

			while (($menu->query['option'] !== 'com_simplefilemanager' || $menu->query['view'] === 'document' || $id != $category->id) && $category->id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => SimplefilemanagerHelperRoute::getCategoryRoute($category->id));
				$category = $category->getParent();
			}

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$this->pathway->addItem($item['title'], $item['link']);
			}
		}

		parent::addFeed();
	}
}
