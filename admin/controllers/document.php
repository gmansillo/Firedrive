<?php

/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @copyright   Firedrive
 */
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Document controller class.
 * @since 5.1.2
 */
class FiredriveControllerDocument extends JControllerForm
{

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since 5.1.2
	 */
	protected $text_prefix = 'COM_FIREDRIVE_DOCUMENT';

	/**
	 * Method to save the form data.
	 *
	 * @param   array $data The form data.
	 *
	 * @return  boolean  True on success.
	 * @since 5.1.2
	 */
	public function save($key = null, $urlVar = null)
	{
		// Load libraries
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.date');

		// Load data
		$input        = JFactory::getApplication()->input;
		$this->form   = $input->post->get('jform', null, 'RAW');
		$this->files  = $input->files->get('jform');
		$this->params = JComponentHelper::getParams('com_firedrive');

		$isNew           = !isset($this->form["file_name"]);
		$uploadFieldName = $isNew ? "select_file" : "replace_file";

		if ($isNew && !$this->files[$uploadFieldName]["size"])
		{
			throw new Exception(JText::_('COM_FIREDRIVE_NO_FILE_ERROR_MESSAGE'), 403);
		}

		// File management
		if ($this->files[$uploadFieldName]["size"])
		{
			// Delete previous file
			if (isset($this->form["file_name"]) && JFile::exists($this->form["file_name"]))
			{
				JFile::delete($this->form["file_name"]);
			}

			// Upload file
			$file_name = $this->files[$uploadFieldName]["name"];
			$dest      = JPATH_COMPONENT_ADMINISTRATOR . "/uploads/" . uniqid("", true) . "/" . JFile::makeSafe(JFile::getName($file_name));
			$upload    = JFile::upload($this->files[$uploadFieldName]["tmp_name"], $dest) ? $dest : false;

			if (!$upload)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_FIREDRIVE_FILE_UPLOAD_ERROR_MESSAGE'), 'error');
				parent::save($key, $urlVar);

				return false;
			}

			$this->form["file_name"] = $upload;
			$this->form["file_size"] = $this->files[$uploadFieldName]["size"];
			$this->form["md5hash"]   = md5_file($this->form["file_name"]);
		}
		else if ($this->task == "save2copy")
		{
			// File copy
			$copy = FiredriveHelper::copyFile($this->form["file_name"]);
			if ($copy === false)
				throw new Exception(JText::sprintf('COM_FIREDRIVE_FIELD_COPY_ERROR', $this->form["file_name"]), 500);

			$this->form["file_name"] = $copy;
		}

		// Set blank values to default
		if ($isNew)
		{
			$this->form["created"]    = JFactory::getDate('now')->format('Y-m-d H:m:s', false);
			$this->form["created_by"] = JFactory::getUser()->id;
		}

		$this->form["modified"]    = JFactory::getDate('now')->format('Y-m-d H:m:s', false);
		$this->form["modified_by"] = JFactory::getUser()->id;

		// If save2copy, controller will automatically change items's id but won't change its publish state
		if ($this->task == "save2copy")
		{
			$this->form["state"] = 0;
		}

		// Save data back to the POST global variable
		JFactory::getApplication()->input->post->set('jform', $this->form);

		return parent::save($key, $urlVar);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   string $model The model
	 *
	 * @return  boolean  True on success.
	 * @since 5.1.2
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Document', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_firedrive&view=documents' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   JModelLegacy $model     The data model object.
	 * @param   array        $validData The validated data.
	 *
	 * @return  void
	 * @since 5.1.2
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = [])
	{
		$item  = $model->getItem();
		$isNew = $this->form["id"] == 0;
		$db    = JFactory::getDbo();

		// Update document user visibility settings
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__firedrive_user_documents'));
		$query->where($db->quoteName('document_id') . ' = ' . $item->id);
		$db->setQuery($query);
		$db->execute();
		if (isset($this->form['reserved_user']))
		{
			foreach ($this->form['reserved_user'] as $u)
			{
				$rel              = new stdClass();
				$rel->user_id     = $u;
				$rel->document_id = $item->id;

				JFactory::getDbo()->insertObject('#__firedrive_user_documents', $rel);
			}
		}

		// Update document group visibility settings
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__firedrive_group_documents'));
		$query->where($db->quoteName('document_id') . ' = ' . $item->id);
		$db->setQuery($query);
		$db->execute();
		if (isset($this->form['reserved_group']))
		{
			foreach ($this->form['reserved_group'] as $g)
			{
				$rel              = new stdClass();
				$rel->group_id    = $g;
				$rel->document_id = $item->id;

				JFactory::getDbo()->insertObject('#__firedrive_group_documents', $rel);
			}
		}

		// Send notify email
		if (($isNew && $this->params->get('sendmail', 0)) || isset($this->form["fl_send_mail"]))
		{
			$recipients = array();

			// Check requisites for email sending
			if ($this->form["state"] != 1)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_FIREDRIVE_SENDMAIL_UNPUBLISHED_DOCUMENT_ERROR'), 'warning');
			}
			elseif ($this->form["visibility"] == 3)
			{
				// Get recipients email from db

				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('user_id')
					->from('#__firedrive_user_documents')
					->where('document_id = ' . $item->id);
				$db->setQuery($query);
				$recipients = $db->loadColumn();
			}
			else if ($this->form["visibility"] == 4)
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('group_id')
					->from('#__firedrive_group_documents')
					->where('document_id = ' . $item->id);
				$db->setQuery($query);
				$group_ids = $db->loadColumn();

				foreach ($group_ids as $g_id)
				{
					$recipients = array_merge($recipients, JAccess::getUsersByGroup($g_id));
				}

				// Remove duplicates
				$recipients = array_unique($recipients, SORT_REGULAR);
			}
			elseif (isset($this->form["fl_send_mail"]))
			{
				// Alert only if send mail is forced
				JFactory::getApplication()->enqueueMessage(JText::_('COM_FIREDRIVE_SENDMAIL_UNSUPPORTED_VISIBILITY_ERROR'), 'warning');
			}

			if (!empty($recipients))
			{
				$config = JFactory::getConfig();
				$sender = [$config->get('mailfrom'), $config->get('fromname')];
				$author = JFactory::getUser((int) $item->created_by);

				foreach ($recipients as $recipient)
				{
					$user = JFactory::getUser((int) $recipient);

					// Message subject
					$subject = JText::_('COM_FIREDRIVE_EMAIL_SUBJ');

					// Message body
					$body = JText::_('COM_FIREDRIVE_EMAIL_BODY');
					$body = str_replace('{user_fullname}', $user->name, $body);
					$body = str_replace('{document_title}', $item->title, $body);
					$body = str_replace('{author_name}', $$author->name, $body);
					$body = str_replace('{site_url}', JUri::base(), $body);

					// Send email
					$mailer = JFactory::getMailer();
					$mailer->setSender($sender);
					$mailer->addRecipient($user->email);
					$mailer->setSubject($subject);
					$mailer->isHTML(true);
					$mailer->Encoding = 'base64';
					$mailer->setBody($body);

					$send = $mailer->Send();

					if (!$send)
					{
						JFactory::getApplication()->enqueueMessage(JText::_('JERROR') . ": " . $send->__toString(), 'error');
					}
				}
			}
		}

		parent::postSaveHook($model, $validData);
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array $data An array of input data.
	 *
	 * @return  boolean
	 * @since 5.1.2
	 */
	protected function allowAdd($data = array())
	{
		$filter     = $this->input->getInt('filter_category_id');
		$categoryId = ArrayHelper::getValue($data, 'catid', $filter, 'int');
		$allow      = null;

		if ($categoryId)
		{
			// If the category has been passed in the URL check it.
			$allow = JFactory::getUser()->authorise('core.create', $this->option . '.category.' . $categoryId);
		}

		if ($allow !== null)
		{
			return $allow;
		}

		// In the absence of better information, revert to the component permissions.
		return parent::allowAdd($data);
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array  $data An array of input data.
	 * @param   string $key  The name of the key for the primary key.
	 *
	 * @return  boolean
	 * @since 5.1.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId   = (int) isset($data[$key]) ? $data[$key] : 0;
		$categoryId = 0;

		if ($recordId)
		{
			$categoryId = (int) $this->getModel()->getItem($recordId)->catid;
		}

		if ($categoryId)
		{
			// The category has been set. Check the category permissions.
			return JFactory::getUser()->authorise('core.edit', $this->option . '.category.' . $categoryId);
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}

}
