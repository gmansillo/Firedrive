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

class SimplefilemanagerModelSimplefilemanagers extends JModelList
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'state', 'a.state',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down',
                'ordering', 'a.ordering',
                'visibility', 'a.visibility',
                'featured', 'a.featured',
                'catid', 'a.catid', 'category_title',
                'download_counter', 'a.download_counter',
                'download_last', 'a.download_last',
                'file_created', 'a.file_created',
                'file_name', 'a.file_name',
                'reserved_user', 'a.reserved_user',
                'reserved_group', 'a.reserved_group',
                'author', 'a.author'
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);

        JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');

        $catID = $this->getUserStateFromRequest($this->context . '.filter.category', 'filter_category');
        $this->setState('filter.category', $catID);

        $visibility = $this->getUserStateFromRequest($this->context . '.filter.visibility', 'filter_visibility');
        $this->setState('filter.category', $visibility);

        parent::populateState('a.ordering', 'asc');
    }

    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.catid,' .
                'a.state, a.featured,' .
                'a.publish_up, a.publish_down, a.ordering,' .
                'a.checked_out, a.checked_out_time,' .
                'a.download_counter, a.download_last, a.file_created, a.file_name, a.reserved_user, a.reserved_group, a.visibility, a.author'
            )
        );
        $query->from($db->quoteName('#__simplefilemanager') . ' AS a');

        $published = $this->getState('filter.state');
        if (is_numeric($published))
        {
            $query->where('a.state = ' . (int)$published);
        }
        elseif ($published === '')
        {
            $query->where('(a.state IN (0, 1))');
        }

        $catID = $db->escape($this->getState('filter.category'));
        if (!empty($catID))
        {
            $query->where('(a.catid=' . $catID . ')');
        }

        $visibility = $db->escape($this->getState('filter.visibility'));
        if (!empty($visibility))
        {
            $query->where('(a.visibility=' . $visibility . ')');
        }

        // Join over the categories.
        $query->select('c.title AS category_title')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid');

        // Join over users.
        $query->select('uc.name AS editor')
            ->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            if (stripos($search, 'id:') === 0)
            {
                $query->where('a.id = ' . (int)substr($search, 3));
            }
            else
            {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('(a.title LIKE ' . $search . ' OR a.file_name LIKE ' . $search . ')');
            }
        }

        $orderCol  = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol == 'a.ordering')
        {
            $orderCol = 'c.title ' . $orderDirn . ', a.ordering';
        }
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }
}