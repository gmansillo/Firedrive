<?php

/**
 *
 * @package     Simple File Manager
 * @author        Giovanni Mansillo
 *
 * @copyright   Copyright (C) 2005 - 2014 Giovanni Mansillo. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/simplefilemanager.php');
require_once JPATH_COMPONENT . '/controller.php';

/**
 * Simplefilemanager controller class.
 */
class SimplefilemanagerControllerSimplefilemanagerForm extends SimplefilemanagerController
{

    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     */
    public function edit()
    {
        $app = JFactory::getApplication();

        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int)$app->getUserState('com_simplefilemanager.edit.simplefilemanager.id');
        $editId     = $app->input->getInt('id', null, 'array');

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_simplefilemanager.edit.simplefilemanager.id', $editId);

        // Get the model.
        $model = $this->getModel('SimplefilemanagerForm', 'SimplefilemanagerModel');

        // Check out the item
        if ($editId)
        {
            $model->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId)
        {
            $model->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanagerform&layout=edit', false));
    }

    /**
     * Method to save item's data.
     *
     * @return    void
     * @since    1.6
     */
    public function save()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app   = JFactory::getApplication();
        $model = $this->getModel('SimplefilemanagerForm', 'SimplefilemanagerModel');
        $jinput = JFactory::getApplication()->input;

        // Get the user data.
        $data = $jinput->get('jform', array(), 'array');
        $files = $jinput->files->get('jform1');

        $this->prepareDataBeforeSave($data, $files);

        // Validate the posted data.
        $form = $model->getForm();
        if (!$form)
        {
            JError::raiseError(500, $model->getError());
            return false;
        }

        // Validate the posted data.
        $data = $model->validate($form, $data);

        // Check for errors.
        if ($data === false)
        {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
            {
                if ($errors[$i] instanceof Exception)
                {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                }
                else
                {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            $input = $app->input;
            $jform = $input->get('jform', array(), 'ARRAY');

            // Save the data in the session.
            $app->setUserState('com_simplefilemanager.edit.simplefilemanager.data', $jform, array());

            // Redirect back to the edit screen.
            $id = (int)$app->getUserState('com_simplefilemanager.edit.simplefilemanager.id');
            $this->setRedirect(JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanagerform&layout=edit&id=' . $id, false));
            return false;
        }

        // Attempt to save the data.
        $return = $model->save($data);

        // Check for errors.
        if ($return === false)
        {
            // Save the data in the session.
            $app->setUserState('com_simplefilemanager.edit.simplefilemanager.data', $data);

            // Redirect back to the edit screen.
            $id = (int)$app->getUserState('com_simplefilemanager.edit.simplefilemanager.id');
            $this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanagerform&layout=edit&id=' . $id, false));
            return false;
        }


        // Check in the profile.
        if ($return)
        {
            $model->checkin($return);
        }

        // Clear the profile id from the session.
        $app->setUserState('com_simplefilemanager.edit.simplefilemanager.id', null);

        // Redirect to the list screen.
        $this->setMessage(JText::_('COM_SIMPLEFILEMANAGER_ITEM_SAVED_SUCCESSFULLY'));
        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url  = (empty($item->link) ? 'index.php?option=com_simplefilemanager&view=simplefilemanagers' : $item->link);
        $this->setRedirect(JRoute::_($url, false));

        // Flush the data from the session.
        $app->setUserState('com_simplefilemanager.edit.simplefilemanager.data', null);
    }

    /**
     * Prepare data before executing controller save function
     * @param mixed $data Form data (passed by reference)
     * @param unknown $files File to upload
     */
    protected function prepareDataBeforeSave(&$data, $files)
    {

        $param = JComponentHelper::getParams('com_simplefilemanager');

        // Check if it's a new item
        $isNew = $data["file_name"] == "" ? 1 : 0;

        // Check if no file has been selected
        if ($isNew and $files['test'][0]["size"] <= 0)
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_FIELD_NOFILE_ERROR'), 'error');
            return;
        }

        // Import Joomla!'s file library
        jimport('joomla.filesystem.file');

        // Check if item already have an old file and delete it
        if (!$isNew and $data["file_size"] > 0)
        {
            if (SimplefilemanagerHelper::deleteFile($data["file_name"]))
            {
                $path_parts = pathinfo($data["file_name"]);
                rmdir($path_parts['dirname']);
            }
        }

        // Check if item needs a file upload
        if ($files['test'][0]["size"])
        {
            // Set up the source and destination of the file
            $src = $files['test'][0]['tmp_name'];

            // Check file extension
            if (!SimplefilemanagerHelper::hasSafeExtension($files['test'][0]["name"]))
            {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_FIELD_DANGEROUS_FILE_ERROR'), 'error');
                return;
            }

            // File upload
            $upload = SimplefilemanagerHelper::uploadFile($files['test'][0]["tmp_name"], $files['test'][0]["name"]);
            if (!$upload)
            {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_FIELD_UPLOAD_ERROR'), 'error');
                return;
            }

            $data["file_name"] = $upload;

            // Set MD5 Hash
            $data["md5hash"]   = md5_file($data["file_name"]);
            $data["file_size"] = $files['test'][0]["size"];

            // Send email
            if ($param->get('sendmail') == 1 and $isNew == 1 and $data["reserved_user"] > 0 and $data["visibility"] == 3)
            {
                $send = SimplefilemanagerHelper::sendMail($data["reserved_user"]);
                if (!send)
                {
                    JFactory::getApplication()->enqueueMessage(JText::_('JERROR') . ": " . $send->__toString(), 'error');
                }
            }
        }

        if ($data["title"] == "")
        {
            $data["title"] = JFile::stripExt($files['test'][0]["name"]);
        }

        if (!$data["author"])
        {
            $data["author"] = JFactory::getUser()->id;
        }

        $data["state"] = 0;

        // Updating dates
        if ($isNew)
        {
            jimport('joomla.utilities.date');
            $date                 = &JFactory::getDate('now');
            $data["file_created"] = $date->format('Y-m-d H:m:s', false);
        }

        if (!$data["md5hash"])
        {
            $data["md5hash"] = md5_file($data["file_name"]);
        }

        return;
    }

    function cancel()
    {

        $app = JFactory::getApplication();

        // Get the current edit id.
        $editId = (int)$app->getUserState('com_simplefilemanager.edit.simplefilemanager.id');

        // Get the model.
        $model = $this->getModel('SimplefilemanagerForm', 'SimplefilemanagerModel');

        // Check in the item
        if ($editId)
        {
            $model->checkin($editId);
        }

        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url    = (empty($item->link) ? 'index.php?option=com_simplefilemanager&view=simplefilemanagers' : $item->link);
        $this->setRedirect(JRoute::_($url, false));
    }

    public function remove()
    {
        // Initialise variables.
        $app = JFactory::getApplication();
        $model = $this->getModel('SimplefilemanagerForm', 'SimplefilemanagerModel');

        // Get the user data.
        $data = array();
        $data['id'] = $app->input->getInt('id');

        // Check for errors.
        if (empty($data['id']))
        {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
            {
                if ($errors[$i] instanceof Exception)
                {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                }
                else
                {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState('com_simplefilemanager.edit.simplefilemanager.data', $data);

            // Redirect back to the edit screen.
            $id = (int)$app->getUserState('com_simplefilemanager.edit.simplefilemanager.id');
            $this->setRedirect(JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanager&layout=edit&id=' . $id, false));
            return false;
        }

        // Attempt to save the data.
        $return = $model->delete($data);

        // Check for errors.
        if ($return === false)
        {
            // Save the data in the session.
            $app->setUserState('com_simplefilemanager.edit.simplefilemanager.data', $data);

            // Redirect back to the edit screen.
            $id = (int)$app->getUserState('com_simplefilemanager.edit.simplefilemanager.id');
            $this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanager&layout=edit&id=' . $id, false));
            return false;
        }


        // Check in the profile.
        if ($return)
        {
            $model->checkin($return);
        }

        // Clear the profile id from the session.
        $app->setUserState('com_simplefilemanager.edit.simplefilemanager.id', null);

        // Redirect to the list screen.
        $this->setMessage(JText::_('COM_SIMPLEFILEMANAGER_ITEM_DELETED_SUCCESSFULLY'));
        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url  = (empty($item->link) ? 'index.php?option=com_simplefilemanager&view=simplefilemanagers' : $item->link);
        $this->setRedirect(JRoute::_($url, false));

        // Flush the data from the session.
        $app->setUserState('com_simplefilemanager.edit.simplefilemanager.data', null);
    }

}
