<?php

/**
 *
 * @package     Simple File Manager
 * @author        Giovanni Mansillo
 *
 * @copyright   Copyright (C) 2005 - 2014 Giovanni Mansillo. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();
require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/simplefilemanager.php');

class SimplefilemanagerControllerSimplefilemanager extends JControllerForm
{
    protected $form;
    protected $files;
    protected $params;

    public function save($key = null, $urlVar = null)
    {
        // Load libraries
        jimport('joomla.filesystem.file');
        jimport('joomla.utilities.date');

        // Load data
        $this->form = JFactory::getApplication()->input->post->get('jform', NULL, 'RAW');
        $this->files  = JFactory::getApplication()->input->files->get('jform1');
        $this->params = JComponentHelper::getParams('com_simplefilemanager');

        // Check if user is creating a new file
        $isNew = $this->form["file_name"] ? false : true;

        // Checking file selection
        if ($this->files['test'][0]["size"])
        {

            // Checking file extension
            if (!SimplefilemanagerHelper::hasSafeExtension($this->files['test'][0]["name"]))
            {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_FIELD_DANGEROUS_FILE_ERROR'), 'error');
                parent::save($key, $urlVar);
                return;
            }

            // Deleting previous file
            if ($this->form["file_name"])
            {
                SimplefilemanagerHelper::deleteFile($this->form["file_name"]);
            }

            // File upload
            $upload = SimplefilemanagerHelper::uploadFile($this->files['test'][0]["tmp_name"], $this->files['test'][0]["name"]);
            if (!$upload or $this->form["file_name"] == $upload)
            {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_FIELD_UPLOAD_ERROR'), 'error');
                parent::save($key, $urlVar);
                return;
            }

            $this->form["file_name"]  = $upload;
            $this->form["file_size"]  = $this->files['test'][0]["size"];
            $this->form["md5hash"]    = md5_file($this->form["file_name"]);

            if (!$this->form["file_created"] or !$this->form["author"])
            {
                $this->form["file_created"] = &JFactory::getDate('now')->format('Y-m-d H:m:s', false);
                $this->form["author"] = JFactory::getUser()->id;
            }

        }
        elseif ($isNew)
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_FIELD_NOFILE_ERROR'), 'error');
            parent::save($key, $urlVar);
            return;
        }

        // Send notify email
        if (($isNew and $this->params->get('sendmail')) or isset($this->form["fl_send_mail"]))
        {
            SimplefilemanagerHelper::sendMail($this->form);
        }

        // Save data back to the $_POST global variable
        JFactory::getApplication()->input->post->set('jform', $this->form);

        parent::save($key, $urlVar);
    }

    protected function allowAdd($data = array())
    {
        $user       = JFactory::getUser();
        $categoryId = JArrayHelper::getValue($data, 'catid', $this->input->getInt('filter_category_id'), 'int');
        $allow      = null;

        if ($categoryId)
        {
            // If the category has been passed in the URL check it.
            $allow = $user->authorise('core.create', $this->option . '.category.' . $categoryId);
        }

        if ($allow === null)
        {
            // In the absense of better information, revert to the component permissions.
            return parent::allowAdd($data);
        }
        else
        {
            return $allow;
        }
    }

    protected function allowEdit($data = array(), $key = 'id')
    {
        $recordId   = (int)isset($data[$key]) ? $data[$key] : 0;
        $categoryId = 0;

        if ($recordId)
        {
            $categoryId = (int)$this->getModel()->getItem($recordId)->catid;
        }

        if ($categoryId)
        {
            // The category has been set. Check the category permissions.
            return JFactory::getUser()->authorise('core.edit', $this->option . '.category.' . $categoryId);
        }
        else
        {
            // Since there is no asset tracking, revert to the component permissions.
            return parent::allowEdit($data, $key);
        }
    }

}