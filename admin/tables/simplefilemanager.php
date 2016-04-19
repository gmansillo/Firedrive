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

class SimplefilemanagerTableSimplefilemanager extends JTable
{
    protected $tagsHelper = null;

    public function __construct(&$db)
    {
        parent::__construct('#__simplefilemanager', 'id', $db);

        $this->tagsHelper            = new JHelperTags();
        $this->tagsHelper->typeAlias = 'com_simplefilemanager.simplefilemanager';
    }

    public function bind($array, $ignore = '')
    {
        if (isset($array['metadata']) && is_array($array['metadata']))
        {
            $registry = new JRegistry;
            $registry->loadArray($array['metadata']);
            $array['metadata'] = (string)$registry;
        }

        return parent::bind($array, $ignore);
    }

    public function store($updateNulls = false)
    {
        $this->tagsHelper->preStoreProcess($this);
        $result = parent::store($updateNulls);
        return $result && $this->tagsHelper->postStoreProcess($this);
    }

    public function publish($pks = null, $state = 1, $userId = 0)
    {
        $k = $this->_tbl_key;

        JArrayHelper::toInteger($pks);
        $state = (int)$state;

        if (empty($pks))
        {
            if ($this->$k)
            {
                $pks = array($this->$k);
            }
            else
            {
                $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        $where = $k . '=' . implode(' OR ' . $k . '=', $pks);

        $query = $this->_db->getQuery(true)
            ->update($this->_db->quoteName($this->_tbl))
            ->set($this->_db->quoteName('state') . ' = ' . (int)$state)
            ->where($where);
        $this->_db->setQuery($query);

        try
        {
            $this->_db->execute();
        }
        catch (RuntimeException $e)
        {
            $this->setError($e->getMessage());
            return false;
        }

        if (in_array($this->$k, $pks))
        {
            $this->state = $state;
        }

        $this->setError('');

        return true;
    }

    public function delete($pk = null)
    {
        $result = parent::delete($pk);
        return $result && $this->tagsHelper->deleteTagData($this, $pk);
    }
}