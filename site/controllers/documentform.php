<?php

/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @copyright   Firedrive
 */
// No direct access
defined('_JEXEC') or die;
require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/firedrive.php');
require_once JPATH_COMPONENT . '/controller.php';

/**
 * Firedrive controller class.
 * @since   5.2.1
 */
class FiredriveControllerDocumentForm extends FiredriveController
{

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @since   5.2.1
	 *
	 */
	public function edit()
	{
		$app = JFactory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_firedrive.edit.document.id');
		$editId     = $app->input->getInt('id', null);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_firedrive.edit.document.id', $editId);

		// Get the model.
		$model = $this->getModel('DocumentForm', 'FiredriveModel');

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
		$this->setRedirect(JRoute::_('index.php?option=com_firedrive&view=documentform&layout=edit', false));
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
		$app    = JFactory::getApplication();
		$model  = $this->getModel('DocumentForm', 'FiredriveModel');
		$jinput = JFactory::getApplication()->input;
        $params = JComponentHelper::getParams('com_firedrive');

        // Get the user data.
		$data  = $jinput->get('jform', array(), 'array');
		$files = $jinput->files->get('jform');

		$this->prepareDataBeforeSave($data, $files);

		// Validate the posted data.
		$form = $model->getForm();
		if (!$form)
		{
			throw new Exception($model->getError(), 500);
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
			$app->setUserState('com_firedrive.edit.document.data', $jform);

			// Redirect back to the edit screen.
			// TODO: Add menu param in xml for customizing redirect url
			$id                   = (int) $app->getUserState('com_firedrive.edit.document.id');
			$default_redirect_url = JRoute::_('index.php?option=com_firedrive&view=documentform&layout=edit&id=' . $id);
			$redirect_url         = $params->get('documentform_redirect', $default_redirect_url);
			$this->setRedirect($redirect_url, false);

			return false;

			// TODO: Set redirect to the file page if file is published
			// TODO: to create documentform_redirect param in menu item options
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_firedrive.edit.document.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_firedrive.edit.document.id');
			$this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_firedrive&view=documentform&layout=edit&id=' . $id, false));

			return false;
		}


		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_firedrive.edit.document.id', null);

		// Redirect to the list screen.
		$this->setMessage(JText::_('COM_FIREDRIVE_ITEM_SAVED_SUCCESSFULLY'));

		$url = JRoute::_('index.php?option=com_firedrive&catid=' . $data["catid"], false);
		$this->setRedirect($url);

		// Flush the data from the session.
		$app->setUserState('com_firedrive.edit.document.data', null);
	}

	/**
	 * Prepare data before executing controller save function
	 *
	 * @param mixed   $data  Form data (passed by reference)
	 * @param unknown $files File to upload
	 *
	 * @since   5.2.1
	 */
	protected function prepareDataBeforeSave(&$data, $files)
	{
		jimport('joomla.filesystem.file');

		$params       = JComponentHelper::getParams('com_firedrive');
		$user         = JFactory::getUser();
		$canManage    = $user->authorise('core.manage', 'com_firedrive');
		$canEditState = $user->authorise('core.edit.state', 'com_firedrive');
		$isNew        = empty($data["id"]);

		if ($files["select_file"]["size"] <= 0)
		{
			if ($isNew)
			{
				throw new Exception(JText::_('COM_FIREDRIVE_NO_FILE_ERROR_MESSAGE'));
			}
		}
		else
		{

			if (!$isNew)
			{
				// Delete old file
				JFile::delete($data["file_name"]);
			}

			// Checking file extension
			// TODO: Implement file extension check
			// File upload
			$file_name         = $files["select_file"]["name"];
			$dest              = JPATH_COMPONENT_ADMINISTRATOR . "/uploads/" . uniqid("", true) . "/" . JFile::makeSafe(JFile::getName($file_name));
			$data["file_name"] = JFile::upload($files["select_file"]["tmp_name"], $dest) ? $dest : false;

			if (!$data["file_name"])
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_FIREDRIVE_FILE_UPLOAD_ERROR_MESSAGE'), 'error');
				parent::save();

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

	function cancel()
	{

		$app = JFactory::getApplication();

		// Get the current edit id.
		$editId = (int) $app->getUserState('com_firedrive.edit.document.id');

		// Get the model.
		$model = $this->getModel('DocumentForm', 'FiredriveModel');

		// Check in the item
		if ($editId)
		{
			$model->checkin($editId);
		}

		$menu = JFactory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_firedrive' : $item->link);
		$this->setRedirect(JRoute::_($url, false));
	}

	public function remove()
	{
		// Initialise variables.
		$app   = JFactory::getApplication();
		$model = $this->getModel('DocumentForm', 'FiredriveModel');

		// Get the user data.
		$data       = array();
		$data["id"] = $app->input->getInt('id');

		// Check for errors.
		if (empty($data["id"]))
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
			$app->setUserState('com_firedrive.edit.document.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_firedrive.edit.document.id');
			$this->setRedirect(JRoute::_('index.php?option=com_firedrive&view=firedrive&layout=edit&id=' . $id, false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->delete($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_firedrive.edit.document.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_firedrive.edit.document.id');
			$this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_firedrive&view=firedrive&layout=edit&id=' . $id, false));

			return false;
		}


		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_firedrive.edit.document.id', null);

		// Redirect to the list screen.
		$this->setMessage(JText::_('COM_FIREDRIVE_ITEM_DELETED_SUCCESSFULLY'));
		$menu = JFactory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_firedrive' : $item->link);
		$this->setRedirect(JRoute::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_firedrive.edit.document.data', null);
	}

}
