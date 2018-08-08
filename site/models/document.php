<?php

/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Single item model for a document
 */
class SimplefilemanagerModelDocument extends JModelItem {

    /**
     * The name of the view for a single item
     */
    protected $view_item = 'document';

    /**
     * A loaded item
     */
    protected $_item = null;

    /**
     * Model context string.
     *
     * @var		string
     */
    protected $_context = 'com_simplefilemanager.document';

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     */
    protected function populateState() {
        $app = JFactory::getApplication();

        $this->setState('document.id', $app->input->getInt('id'));
        $this->setState('params', $app->getParams());

        $user = JFactory::getUser();

        if ((!$user->authorise('core.edit.state', 'com_simplefilemanager')) && (!$user->authorise('core.edit', 'com_simplefilemanager'))) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }
    }

    /**
     * Gets a document
     *
     * @param   integer  $pk  Id for the document
     *
     * @return  mixed Object or null
     *
     * @since   1.6.0
     */
    public function &getItem($pk = null) {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('document.id');

        if ($this->_item === null) {
            $this->_item = array();
        }

        if (!isset($this->_item[$pk])) {
            try {
                $db = $this->getDbo();
                $query = $db->getQuery(true);

                // Changes for sqlsrv
                $case_when = ' CASE WHEN ';
                $case_when .= $query->charLength('a.alias', '!=', '0');
                $case_when .= ' THEN ';
                $a_id = $query->castAsChar('a.id');
                $case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
                $case_when .= ' ELSE ';
                $case_when .= $a_id . ' END as slug';

                $case_when1 = ' CASE WHEN ';
                $case_when1 .= $query->charLength('c.alias', '!=', '0');
                $case_when1 .= ' THEN ';
                $c_id = $query->castAsChar('c.id');
                $case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
                $case_when1 .= ' ELSE ';
                $case_when1 .= $c_id . ' END as catslug';

                $query->select($this->getState('item.select', 'a.*') . ',' . $case_when . ',' . $case_when1)
                        ->from('#__simplefilemanager AS a')

                        // Join on category table.
                        ->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access')
                        ->join('LEFT', '#__categories AS c on c.id = a.catid')

                        // Join on user table.
                        ->select('u.name as created_by_name')
                        ->join('LEFT', '#__users AS u on a.created_by = u.id')

                        // Join over the categories to get parent category titles
                        ->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
                        ->join('LEFT', '#__categories as parent ON parent.id = c.parent_id')
                        ->where('a.id = ' . (int) $pk);

                // Filter by start and end dates.
                $nullDate = $db->quote($db->getNullDate());
                $nowDate = $db->quote(JFactory::getDate()->toSql());

                // Filter by published state.
                $published = $this->getState('filter.published');
                $archived = $this->getState('filter.archived');

                if (is_numeric($published)) {
                    $query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')')
                            ->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
                            ->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
                }

                $db->setQuery($query);
                $data = $db->loadObject();

                if (empty($data)) {
                    throw new Exception(JText::_('COM_SIMPLEFILEMANAGER_ERROR_DOCUMENT_NOT_FOUND'), 404);
                }

                // Process document icon
                SimplefilemanagerHelper::processDocumentIcon($data);

                // Check for published state if filter set.
                if ((is_numeric($published) || is_numeric($archived)) && (($data->state != $published) && ($data->state != $archived))) {
                    throw new Exception(JText::_('COM_SIMPLEFILEMANAGER_ERROR_DOCUMENT_NOT_FOUND'), 404);
                }

                /**
                 * In case some entity params have been set to "use global", those are
                 * represented as an empty string and must be "overridden" by merging
                 * the component and / or menu params here.
                 */
                $registry = new Registry($data->params);

                $data->params = clone $this->getState('params');
                $data->params->merge($registry);

                $registry = new Registry($data->metadata);
                $data->metadata = $registry;

                // Some contexts may not use tags data at all, so we allow callers to disable loading tag data
                if ($this->getState('load_tags', true)) {
                    $data->tags = new JHelperTags;
                    $data->tags->getItemTags('com_simplefilemanager.document', $data->id);
                }

                // Compute access permissions.
                if (($access = $this->getState('filter.access'))) {
                    // If the access filter has been set, we already know this user can view.
                    $data->params->set('access-view', true);
                } else {
                    // If no access filter is set, the layout takes some responsibility for display of limited information.
                    $user = JFactory::getUser();
                    $groups = $user->getAuthorisedViewLevels();

                    if ($data->catid == 0 || $data->category_access === null) {
                        $data->params->set('access-view', in_array($data->access, $groups));
                    } else {
                        $data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
                    }
                }

                $this->_item[$pk] = $data;
            } catch (Exception $e) {
                $this->setError($e);
                $this->_item[$pk] = false;
            }
        }

        return $this->_item[$pk];
    }

    /**
     * Increment the download counter for the document.
     *
     * @param   integer  $pk  Optional primary key of the document to increment.
     *
     * @return  void
     */
    public function countDownload() {
        $pk = (int) $this->getState('document.id');

        $params = JComponentHelper::getParams('com_simplefilemanager');
        $user = JFactory::getUser();
        $now = JFactory::getDate();
        $db = JFactory::getDbo();
        $nowDate = $now->toSql();

        $fields = array(
            $db->quoteName('download_counter') . ' = ' . $db->quoteName('download_counter') . '+1',
            $db->quoteName('download_last') . ' = ' . $db->quote($nowDate)
        );

        $query = $db->getQuery(true);
        $query
                ->update($db->quoteName('#__simplefilemanager'))
                ->set($fields)
                ->where($db->quoteName('id') . ' = ' . $pk);
        $db->setQuery($query);
        $db->execute();

        if ($params->get('track_user_downloads', 0) == 1 && !$user->guest) {

            $ip_address = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            $columns = array('document_id', 'user_id', 'download_time', 'ip_address');
            $values = array($pk, $user->id, $db->quote($nowDate), $db->quote($ip_address));

            $query = $db->getQuery(true);
            $query
                    ->insert($db->quoteName('#__simplefilemanager_download_tracking'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));
            $db->setQuery($query);
            $db->execute();
        }

        return;
    }

}
