<?php

/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Single item model for a document
 */
class FiredriveModelCategory extends JModelList {

    /**
     * Category items data
     *
     * @var array
     */
    protected $_item     = null;
    protected $_articles = null;
    protected $_siblings = null;
    protected $_children = null;
    protected $_parent   = null;

    /**
     * The category that applies.
     *
     * @access    protected
     * @var        object
     */
    protected $_category = null;

    /**
     * The list of other document categories.
     *
     * @access    protected
     * @var       array
     */
    protected $_categories = null;

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'state', 'a.state',
                'ordering', 'a.ordering',
                'description', 'a.description',
                'created', 'a.created',
                'file_size', 'a.file_size',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to get a list of items.
     *
     * @return  mixed  An array of objects on success, false on failure.
     */
    public function getItems() {
        // Invoke the parent getItems method to get the main list
        $items = parent::getItems();

        // Convert the params field into an object, saving original in _params
        for ($i = 0, $n = count($items); $i < $n; $i++) {
            $item = &$items[$i];

            if (!isset($this->_params)) {
                $item->params = new Registry($item->params);
            }

            // Process document icon
            FiredriveHelper::processDocumentIcon($item);

            // Some contexts may not use tags data at all, so we allow callers to disable loading tag data
            if ($this->getState('load_tags', true)) {
                $this->tags = new JHelperTags;
                $this->tags->getItemTags('com_firedrive.document', $item->id);
            }
        }

        return $items;
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return  string    An SQL query
     *
     * @since   1.6
     */
    protected function getListQuery() {
        jimport('joomla.access.access');

        $user        = JFactory::getUser();
        $groups      = implode(',', $user->getAuthorisedViewLevels());
        $user_groups = JAccess::getGroupsByUser($user->id, true);

        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select required fields from the categories.
        // Changes for sqlsrv
        $case_when = ' CASE WHEN ';
        $case_when .= $query->charLength('a.alias', '!=', '0');
        $case_when .= ' THEN ';
        $a_id      = $query->castAsChar('a.id');
        $case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
        $case_when .= ' ELSE ';
        $case_when .= $a_id . ' END as slug';

        $case_when1 = ' CASE WHEN ';
        $case_when1 .= $query->charLength('c.alias', '!=', '0');
        $case_when1 .= ' THEN ';
        $c_id       = $query->castAsChar('c.id');
        $case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
        $case_when1 .= ' ELSE ';
        $case_when1 .= $c_id . ' END as catslug';
        $query->select($this->getState('list.select', 'DISTINCT a.*') . ',' . $case_when . ',' . $case_when1)
                /**
                 * TODO: we actually should be doing it but it's wrong this way
                 * 	. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
                 * 	. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END AS catslug ');
                 */
                ->from($db->quoteName('#__firedrive') . ' AS a')
                ->join('LEFT', '#__categories AS c ON c.id = a.catid');
        // Following statement not required since we do not use access column but we relay on firedrive acls
        // ->where('a.access IN (' . $groups . ')');
        // Join on user table.
        // TODO: Check if following code can be removed
        $query->select('u.name as created_by_name')
                ->join('LEFT', '#__users AS u on a.created_by = u.id');

        // Filter by category.
        if ($categoryId = $this->getState('category.id')) {
            $query->where('a.catid = ' . (int) $categoryId)
                    ->where('c.access IN (' . $groups . ')');
        }

        // Join over the users for the author and modified_by names.
        $query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author")
                ->select('ua.email AS author_email')
                ->join('LEFT', '#__users AS ua ON ua.id = a.created_by')
                ->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');

        // Filter by internal ACLs
        $acl = [];

        // Public documents
        $acl[] = "  a.visibility = 1";

        if (!$user->guest) {
            // Registred user documents
            $acl[] = "a.visibility = 2";

            // Selected users documents
            $query->join('LEFT', '#__firedrive_user_documents ud ON a.id = ud.document_id AND a.visibility = 3');
            $acl[] = $db->quoteName('ud.user_id') . " = $user->id";

            // Selected groups documents
            $query->join('LEFT', '#__firedrive_group_documents gd ON a.id = gd.document_id AND a.visibility = 4');
            $acl[] = "gd.group_id IN (" . implode(',', $user_groups) . ")";

            // Author documents
            $acl[] = " a.visibility = 5 AND a.created_by = " . (int) $user->id;

            // TODO: Add visibility based on access levels (using $groups instead of $user_groups) and filling 'access' db column
        }

        $query->where('(( ' . implode(' ) OR ( ', $acl) . ' ))')->group('a.id');

        // Filter by state
        $state = $this->getState('filter.published');

        if (is_numeric($state)) {
            $query->where('a.state = ' . (int) $state);
        } else {
            $query->where('(a.state IN (0,1,2))');
        }

        // Filter by start and end dates.
        $nullDate = $db->quote($db->getNullDate());
        $nowDate  = $db->quote(JFactory::getDate()->toSql());

        if ($this->getState('filter.publish_date')) {
            $query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
                    ->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
        }

        // Filter by search in title
        $search = $this->getState('list.filter');

        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(a.title LIKE ' . $search . ')');
        }

        // Filter by language
        if ($this->getState('filter.language')) {
            $query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        }

        // Set sortname ordering if selected
        $query->order($db->escape($this->getState('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

        return $query;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null) {
        $app    = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_firedrive');

        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'uint');

        $this->setState('list.limit', $limit);

        $limitstart = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $limitstart);

        // Optional filter text
        $itemid = $app->input->get('Itemid', 0, 'int');
        $search = $app->getUserStateFromRequest('com_firedrive.category.list.' . $itemid . '.filter-search', 'filter-search', '', 'string');
        $this->setState('list.filter', $search);

        // Get list ordering default from the parameters
        $menuParams = new Registry;

        if ($menu = $app->getMenu()->getActive()) {
            $menuParams->loadString($menu->params);
        }

        $mergedParams = clone $params;
        $mergedParams->merge($menuParams);

        $orderCol = $app->input->get('filter_order', $mergedParams->get('initial_sort', 'ordering'));

        if (!in_array($orderCol, $this->filter_fields)) {
            $orderCol = 'ordering';
        }

        $this->setState('list.ordering', $orderCol);

        $listOrder = $app->input->get('filter_order_Dir', 'ASC');

        if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
            $listOrder = 'ASC';
        }

        $this->setState('list.direction', $listOrder);

        $id = $app->input->get('id', 0, 'int');
        $this->setState('category.id', $id);

        $user = JFactory::getUser();

        if ((!$user->authorise('core.edit.state', 'com_firedrive')) && (!$user->authorise('core.edit', 'com_firedrive'))) {
            // Limit to published for people who can't edit or edit.state.
            $this->setState('filter.published', 1);

            // Filter by start and end dates.
            $this->setState('filter.publish_date', true);
        }

        $this->setState('filter.language', JLanguageMultilang::isEnabled());

        // Load the parameters.
        $this->setState('params', $params);
    }

    /**
     * Method to get category data for the current category
     *
     * @return  object  The category object
     *
     * @since   1.5
     */
    public function getCategory() {
        if (!is_object($this->_item)) {
            $app    = JFactory::getApplication();
            $menu   = $app->getMenu();
            $active = $menu->getActive();
            $params = new Registry;

            if ($active) {
                $params->loadString($active->params);
            }

            $options               = array();
            $options['countItems'] = $params->get('show_cat_items', 1) || $params->get('show_empty_categories', 0);
            $categories            = JCategories::getInstance('Firedrive', $options);
            $this->_item           = $categories->get($this->getState('category.id', 'root'));

            if (is_object($this->_item)) {
                $this->_children = $this->_item->getChildren();
                $this->_parent   = false;

                if ($this->_item->getParent()) {
                    $this->_parent = $this->_item->getParent();
                }

                $this->_rightsibling = $this->_item->getSibling();
                $this->_leftsibling  = $this->_item->getSibling(false);
            } else {
                $this->_children = false;
                $this->_parent   = false;
            }
        }

        return $this->_item;
    }

    /**
     * Get the parent category.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    public function getParent() {
        if (!is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_parent;
    }

    /**
     * Get the sibling (adjacent) categories.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    public function &getLeftSibling() {
        if (!is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_leftsibling;
    }

    /**
     * Get the sibling (adjacent) categories.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    public function &getRightSibling() {
        if (!is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_rightsibling;
    }

    /**
     * Get the child categories.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    public function &getChildren() {
        if (!is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_children;
    }

}
