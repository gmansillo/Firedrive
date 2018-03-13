<?php

/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;

/**
 * Routing class from com_simplefilemanager
 *
 * @since  3.3
 */
class SimplefilemanagerRouter extends JComponentRouterView {

    protected $noIDs = false;

    /**
     * Search Component router constructor
     *
     * @param   JApplicationCms  $app   The application object
     * @param   JMenu            $menu  The menu object to work with
     */
    public function __construct($app = null, $menu = null) {
        $params      = JComponentHelper::getParams('com_simplefilemanager');
        $this->noIDs = (bool) $params->get('sef_ids');
        $categories  = new JComponentRouterViewconfiguration('categories');
        $categories->setKey('id');
        $this->registerView($categories);
        $category    = new JComponentRouterViewconfiguration('category');
        $category->setKey('id')->setParent($categories, 'catid')->setNestable();
        $this->registerView($category);
        $document    = new JComponentRouterViewconfiguration('document');
        $document->setKey('id')->setParent($category, 'catid');
        $this->registerView($document);

        parent::__construct($app, $menu);

        $this->attachRule(new JComponentRouterRulesMenu($this));

        if ($params->get('sef_advanced', 0)) {
            $this->attachRule(new JComponentRouterRulesStandard($this));
            $this->attachRule(new JComponentRouterRulesNomenu($this));
        } else {
            JLoader::register('SimplefilemanagerRouterRulesLegacy', __DIR__ . '/helpers/legacyrouter.php');
            $this->attachRule(new SimplefilemanagerRouterRulesLegacy($this));
        }
    }

    /**
     * Method to get the segment(s) for a category
     *
     * @param   string  $id     ID of the category to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getCategorySegment($id, $query) {
        $category = JCategories::getInstance($this->getName())->get($id);

        if ($category) {
            $path    = array_reverse($category->getPath(), true);
            $path[0] = '1:root';

            if ($this->noIDs) {
                foreach ($path as &$segment) {
                    list($id, $segment) = explode(':', $segment, 2);
                }
            }

            return $path;
        }

        return array();
    }

    /**
     * Method to get the segment(s) for a category
     *
     * @param   string  $id     ID of the category to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getCategoriesSegment($id, $query) {
        return $this->getCategorySegment($id, $query);
    }

    /**
     * Method to get the segment(s) for a document
     *
     * @param   string  $id     ID of the document to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getDocumentSegment($id, $query) {
        if (!strpos($id, ':')) {
            $db      = JFactory::getDbo();
            $dbquery = $db->getQuery(true);
            $dbquery->select($dbquery->qn('alias'))
                    ->from($dbquery->qn('#__simplefilemanager'))
                    ->where('id = ' . $dbquery->q((int) $id));
            $db->setQuery($dbquery);

            $id .= ':' . $db->loadResult();
        }

        if ($this->noIDs) {
            list($void, $segment) = explode(':', $id, 2);

            return array($void => $segment);
        }

        return array((int) $id => $id);
    }

    /**
     * Method to get the id for a category
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getCategoryId($segment, $query) {
        if (isset($query['id'])) {
            $category = JCategories::getInstance($this->getName(), array('access' => false))->get($query['id']);

            if ($category) {
                foreach ($category->getChildren() as $child) {
                    if ($this->noIDs) {
                        if ($child->alias == $segment) {
                            return $child->id;
                        }
                    } else {
                        if ($child->id == (int) $segment) {
                            return $child->id;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Method to get the segment(s) for a category
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getCategoriesId($segment, $query) {
        return $this->getCategoryId($segment, $query);
    }

    /**
     * Method to get the segment(s) for a document
     *
     * @param   string  $segment  Segment of the document to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getDocumentId($segment, $query) {
        if ($this->noIDs) {
            $db      = JFactory::getDbo();
            $dbquery = $db->getQuery(true);
            $dbquery->select($dbquery->qn('id'))
                    ->from($dbquery->qn('#__simplefilemanager'))
                    ->where('alias = ' . $dbquery->q($segment))
                    ->where('catid = ' . $dbquery->q($query['id']));
            $db->setQuery($dbquery);

            return (int) $db->loadResult();
        }

        return (int) $segment;
    }

}

/**
 * Simplefilemanager router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function SimplefilemanagerBuildRoute(&$query) {
    $app    = JFactory::getApplication();
    $router = new SimplefilemanagerRouter($app, $app->getMenu());

    return $router->build($query);
}

/**
 * Simplefilemanager router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function SimplefilemanagerParseRoute($segments) {
    $app    = JFactory::getApplication();
    $router = new SimplefilemanagerRouter($app, $app->getMenu());

    return $router->parse($segments);
}
