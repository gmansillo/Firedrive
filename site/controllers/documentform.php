<?php

/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
// No direct access
defined('_JEXEC') or die;
require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/simplefilemanager.php');
require_once JPATH_COMPONENT . '/controller.php';

define('DS', DIRECTORY_SEPARATOR);

/**
 * Simplefilemanager controller class.
 */
class SimplefilemanagerControllerDocumentForm extends SimplefilemanagerController {

    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     */
    public function edit() {
        $app = JFactory::getApplication();

        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int) $app->getUserState('com_simplefilemanager.edit.document.id');
        $editId     = $app->input->getInt('id', null, 'array');

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_simplefilemanager.edit.document.id', $editId);

        // Get the model.
        $model = $this->getModel('DocumentForm', 'SimplefilemanagerModel');

        // Check out the item
        if ($editId) {
            $model->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId) {
            $model->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(JRoute::_('index.php?option=com_simplefilemanager&view=documentform&layout=edit', false));
    }

    /**
     * Method to save item's data.
     *
     * @return    void
     * @since    1.6
     */
    public function save() {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app    = JFactory::getApplication();
        $model  = $this->getModel('DocumentForm', 'SimplefilemanagerModel');
        $jinput = JFactory::getApplication()->input;

        // Get the user data.
        $data  = $jinput->get('jform', array(), 'array');
        $files = $jinput->files->get('jform');

        $this->prepareDataBeforeSave($data, $files);

        // Validate the posted data.
        $form = $model->getForm();
        if (!$form) {
            JError::raiseError(500, $model->getError());

            return false;
        }

        // Validate the posted data.
        $data = $model->validate($form, $data);

        // Check for errors.
        if ($data === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            $input = $app->input;
            $jform = $input->get('jform', array(), 'ARRAY');

            // Save the data in the session.
            $app->setUserState('com_simplefilemanager.edit.document.data', $jform, array());

            // Redirect back to the edit screen.
            // TODO: Add menu param in xml for customizing redirect url
            $id                   = (int) $app->getUserState('com_simplefilemanager.edit.document.id');
            $default_redirect_url = JRoute::_('index.php?option=com_simplefilemanager&view=documentform&layout=edit&id=' . $id);
            $redirect_url         = $params->get('documentform_redirect', $default_redirect_url);
            $this->setRedirect($redirect_url, false);

            return false;
        }

        // Attempt to save the data.
        $return = $model->save($data);

        // Check for errors.
        if ($return === false) {
            // Save the data in the session.
            $app->setUserState('com_simplefilemanager.edit.document.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_simplefilemanager.edit.document.id');
            $this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=com_simplefilemanager&view=documentform&layout=edit&id=' . $id, false));

            return false;
        }


        // Check in the profile.
        if ($return) {
            $model->checkin($return);
        }

        // Clear the profile id from the session.
        $app->setUserState('com_simplefilemanager.edit.document.id', null);

        // Redirect to the list screen.
        $this->setMessage(JText::_('COM_SIMPLEFILEMANAGER_ITEM_SAVED_SUCCESSFULLY'));

        $url = JRoute::_('index.php?option=com_simplefilemanager&catid=' . $data["catid"], false);
        $this->setRedirect($url);

        // Flush the data from the session.
        $app->setUserState('com_simplefilemanager.edit.document.data', null);
    }

    /**
     * Prepare data before executing controller save function
     *
     * @param mixed   $data  Form data (passed by reference)
     * @param unknown $files File to upload
     */
    protected function prepareDataBeforeSave(&$data, $files) {
        jimport('joomla.filesystem.file');

        $params       = JComponentHelper::getParams('com_simplefilemanager');
        $user         = JFactory::getUser();
        $canManage    = $user->authorise('core.manage', 'com_simplefilemanager');
        $canEditState = $user->authorise('core.edit.state', 'com_simplefilemanager');
        $isNew        = empty($data["id"]);

        if ($files["select_file"]["size"] <= 0) {
            if ($isNew) {
                JError::raiseError(403, JText::_('COM_SIMPLEFILEMANAGER_NO_FILE_ERROR_MESSAGE'));
                return;
            }
        } else {

            if (!$isNew) {
                // Delete old file
                JFile::delete($data["file_name"]);
            }

            // Checking file extension
            // TODO: Implement file extension check
            // File upload
            $file_name         = $files["select_file"]["name"];
            $dest              = JPATH_COMPONENT_ADMINISTRATOR . DS . "uploads" . DS . uniqid("", true) . DS . JFile::makeSafe(JFile::getName($file_name));
            $data["file_name"] = JFile::upload($files["select_file"]["tmp_name"], $dest) ? $dest : false;

            if (!$data["file_name"]) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_FILE_UPLOAD_ERROR_MESSAGE'), 'error');
                parent::save($key, $urlVar);

                return;
            }

            $data["file_size"] = $files["select_file"]["size"];
            $data["md5hash"]   = md5_file($data["file_name"]);
        }

        $data["state"]      = $params->get('default_state', 0);
        $data["visibility"] = $params->get('default_visibility', 5);
        $data["created"]    = JFactory::getDate()->toSql();
        $data["created_by"] = $user->id;
        $data["language"]   = "*";

        // Send notify email
        // TODO: Advice administrators via email notification

        return;
    }

    function cancel() {

        $app = JFactory::getApplication();

        // Get the current edit id.
        $editId = (int) $app->getUserState('com_simplefilemanager.edit.document.id');

        // Get the model.
        $model = $this->getModel('DocumentForm', 'SimplefilemanagerModel');

        // Check in the item
        if ($editId) {
            $model->checkin($editId);
        }

        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url  = (empty($item->link) ? 'index.php?option=com_simplefilemanager' : $item->link);
        $this->setRedirect(JRoute::_($url, false));
    }

    public function remove() {
        // Initialise variables.
        $app   = JFactory::getApplication();
        $model = $this->getModel('DocumentForm', 'SimplefilemanagerModel');

        // Get the user data.
        $data       = array();
        $data["id"] = $app->input->getInt('id');

        // Check for errors.
        if (empty($data["id"])) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState('com_simplefilemanager.edit.document.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_simplefilemanager.edit.document.id');
            $this->setRedirect(JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanager&layout=edit&id=' . $id, false));

            return false;
        }

        // Attempt to save the data.
        $return = $model->delete($data);

        // Check for errors.
        if ($return === false) {
            // Save the data in the session.
            $app->setUserState('com_simplefilemanager.edit.document.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_simplefilemanager.edit.document.id');
            $this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanager&layout=edit&id=' . $id, false));

            return false;
        }


        // Check in the profile.
        if ($return) {
            $model->checkin($return);
        }

        // Clear the profile id from the session.
        $app->setUserState('com_simplefilemanager.edit.document.id', null);

        // Redirect to the list screen.
        $this->setMessage(JText::_('COM_SIMPLEFILEMANAGER_ITEM_DELETED_SUCCESSFULLY'));
        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url  = (empty($item->link) ? 'index.php?option=com_simplefilemanager' : $item->link);
        $this->setRedirect(JRoute::_($url, false));

        // Flush the data from the session.
        $app->setUserState('com_simplefilemanager.edit.document.data', null);
    }

}