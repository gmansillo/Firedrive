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

class SimplefilemanagerModelSimplefilemanager extends JModelAdmin
{
    protected $text_prefix = 'COM_SIMPLEFILEMANAGER';

    public function getForm($data = array(), $loadData = true)
    {
        $app = JFactory::getApplication();

        $form = $this->loadForm('com_simplefilemanager.simplefilemanager', 'simplefilemanager', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form))
        {
            return false;
        }

        return $form;
    }

    public function featured($pks, $value = 0)
    {
        // Sanitize the ids.
        $pks = (array)$pks;
        JArrayHelper::toInteger($pks);

        if (empty($pks))
        {
            $this->setError(JText::_('COM_SIMPLEFILEMANAGER_NO_ITEM_SELECTED'));
            return false;
        }

        try
        {
            $db = $this->getDbo();

            $db->setQuery(
                'UPDATE #__simplefilemanager' .
                ' SET featured = ' . (int)$value .
                ' WHERE id IN (' . implode(',', $pks) . ')'
            );
            $db->execute();

        }
        catch (Exception $e)
        {
            $this->setError($e->getMessage());
            return false;
        }

        $this->cleanCache();

        return true;
    }

    public function delete(&$pks)
    {
        $dispatcher = JEventDispatcher::getInstance();
        $pks        = (array)$pks;
        $table      = $this->getTable();
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        $db = JFactory::getDbo();

        // Include the content plugins for the on delete events.
        JPluginHelper::importPlugin('content');

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk)
        {

            if ($table->load($pk))
            {

                if ($this->canDelete($table))
                {

                    $context = $this->option . '.' . $this->name;

                    // Trigger the onContentBeforeDelete event.
                    $result = $dispatcher->trigger($this->event_before_delete, array($context, $table));

                    if (in_array(false, $result, true))
                    {
                        $this->setError($table->getError());
                        return false;
                    }

                    $query = $db->getQuery(true);
                    $query
                        ->select($db->quoteName('file_name'))
                        ->from($db->quoteName('#__simplefilemanager'))
                        ->where($db->quoteName('id') . ' = ' . $pk);
                    $db->setQuery($query);

                    $file_name = $db->loadResult();

                    if (!$table->delete($pk))
                    {
                        $this->setError($table->getError());
                        return false;
                    }
                    else
                    {

                        if (!JFile::delete($file_name))
                        {
                            JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_ERROR_DELETING') . ': ' . $file_name, 'error');
                        }
                        else
                        {
                            $path_parts = pathinfo($file_name);
                            JFolder::delete($path_parts['dirname']);
                        }

                    }
                    // Trigger the onContentAfterDelete event.
                    $dispatcher->trigger($this->event_after_delete, array($context, $table));

                }
                else
                {

                    // Prune items that you can't change.
                    unset($pks[$i]);
                    $error = $this->getError();
                    if ($error)
                    {
                        JLog::add($error, JLog::WARNING, 'jerror');
                        return false;
                    }
                    else
                    {
                        JLog::add(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
                        return false;
                    }
                }

            }
            else
            {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;

    }

    public function getTable($type = 'Simplefilemanager', $prefix = 'SimplefilemanagerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    protected function canDelete($record)
    {
        if (!empty($record->id))
        {
            if ($record->state != -2)
            {
                return;
            }
            $user = JFactory::getUser();

            if ($record->catid)
            {
                return $user->authorise('core.delete', 'com_simplefilemanager.category.' . (int)$record->catid);
            }
            else
            {
                return parent::canDelete($record);
            }
        }
    }

    protected function canEditState($record)
    {
        $user = JFactory::getUser();

        if (!empty($record->catid))
        {
            return $user->authorise('core.edit.state', 'com_simplefilemanager.category.' . (int)$record->catid);
        }
        else
        {
            return parent::canEditState($record);
        }
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState('com_simplefilemanager.edit.simplefilemanager.data', array());

        if (empty($data))
        {
            $data = $this->getItem();
        }

        return $data;
    }

    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk))
        {
            // Convert the metadata field to an array.
            $registry = new JRegistry;
            $registry->loadString($item->metadata);
            $item->metadata = $registry->toArray();

            if (!empty($item->id))
            {
                $item->tags = new JHelperTags;
                $item->tags->getTagIds($item->id, 'com_simplefilemanager.simplefilemanager');
                $item->metadata['tags'] = $item->tags;
            }
        }

        return $item;
    }

    protected function prepareTable($table)
    {
        $table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);
    }

}