<?php
/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Document controller class.
 */
class SimplefilemanagerControllerDocument extends JControllerForm
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
	protected $text_prefix = 'COM_SIMPLEFILEMANAGER_DOCUMENT';

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 */
	public function save($key = null, $urlVar = null)
	{
		// Load libraries
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.date');

		define('DS',DIRECTORY_SEPARATOR);

		// Load data
		$input = JFactory::getApplication()->input;
		$this->form   = $input->post->get('jform', null, 'RAW');
		$this->files  = $input->files->get('jform');
		$this->params = JComponentHelper::getParams('com_simplefilemanager');

		$isNew = !isset($this->form["file_name"]);
		$uploadFieldName = $isNew ? "select_file" : "replace_file";

		if($isNew && !$this->files[$uploadFieldName]["size"])
		{
			JError::raiseError( 403,  JText::_('COM_SIMPLEFILEMANAGER_NO_FILE_ERROR_MESSAGE')); 
			return;
		}

		// File management
		if ($this->files[$uploadFieldName]["size"])
		{
			// Checking file extension
			// if (!SimplefilemanagerHelper::hasSafeExtension($this->files['test'][0]["name"]))
			// {
			// 	JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_FIELD_DANGEROUS_FILE_ERROR'), 'error');
			// 	parent::save($key, $urlVar);

			// 	return;
			// }

			// Delete previous file
			if (!$this->form["file_name"]){
				JFile::delete($this->form["file_name"]);
			}

			// Upload file
			$file_name = $this->files[$uploadFieldName]["name"];
			$dest = JPATH_COMPONENT_ADMINISTRATOR . DS . "uploads" . DS . uniqid( "", true ) . DS . JFile::makeSafe( JFile::getName( $file_name ) );
			$upload = JFile::upload( $this->files[$uploadFieldName]["tmp_name"], $dest ) ? $dest : false;

			if (!$upload || $this->form["file_name"] == $upload)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_FILE_UPLOAD_ERROR_MESSAGE'), 'error');
				parent::save($key, $urlVar);

				return;
			}

			$this->form["file_name"] = $upload;
			$this->form["file_size"] = $this->files[$uploadFieldName]["size"];
			$this->form["md5hash"]   = md5_file($this->form["file_name"]);
		}
		else if ($this->task == "save2copy")
		{
			// File copy
			$copy = SimplefilemanagerHelper::copyFile($this->form["file_name"]);
			if ($copy === false) throw new Exception(JText::sprintf('COM_SIMPLEFILEMANAGER_FIELD_COPY_ERROR', $this->form["file_name"]), 500);

			$this->form["file_name"] = $copy;
		}

		// Set blank values to default
		if ($isNew)
		{
			$this->form["created"] = JFactory::getDate('now')->format('Y-m-d H:m:s', false);
			$this->form["created_by"] = JFactory::getUser()->id;
		}
		
		$this->form["modified"]    = JFactory::getDate('now')->format('Y-m-d H:m:s', false);
		$this->form["modified_by"] = JFactory::getUser()->id;
	
		// If save2copy, controller will automatically change items's id but won't change its publish state
		if ($this->task == "save2copy")
		{
			$this->form["state"] = 0;
		}

		// Save data back to the $_POST global variable
		JFactory::getApplication()->input->post->set('jform', $this->form);

		parent::save($key, $urlVar);
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return  void

	 */
	protected function postSaveHook(JModelLegacy $model, $validData = [])
	{
		$item   = $model->getItem();
		$itemId = $item->get('id');
		$isNew = !isset($this->form["file_name"]);

		// Update document user visibility settings
		$db = JFactory::getDbo();
		$db->setQuery('DELETE FROM ' . $db->quoteName('#__simplefilemanager_user_documents') . ' WHERE ' . $db->quoteName('document_id') . '=' . $itemId)->execute();

		foreach ($this->form['reserved_user'] as $u)
		{
			$rel              = new stdClass();
			$rel->user_id     = $u;
			$rel->document_id = $itemId;

			JFactory::getDbo()->insertObject('#__simplefilemanager_user_documents', $rel);
		}

		// Update document group visibility settings
		$db = JFactory::getDbo();
		$db->setQuery('DELETE FROM ' . $db->quoteName('#__simplefilemanager_group_documents') . ' WHERE ' . $db->quoteName('document_id') . '=' . $itemId)->execute();

		foreach ($this->form['reserved_group'] as $g)
		{
			$rel              = new stdClass();
			$rel->group_id    = $g;
			$rel->document_id = $itemId;

			JFactory::getDbo()->insertObject('#__simplefilemanager_group_documents', $rel);
		}

		// Send notify email
		if (($isNew and $this->params->get('sendmail')) || isset($this->form["fl_send_mail"]))
		{
			// Check requisites for email sending
			if ( $this->form["state"] != 1 ) {
				if(!$isNew) {
					JFactory::getApplication()->enqueueMessage( JText::_( 'COM_SIMPLEFILEMANAGER_SENDMAIL_UNPUBLISHED_DOCUMENT_ERROR' ), 'warning' );
				}
			} elseif ( !in_array($this->form["visibility"], array(3,4)) ) {
				JFactory::getApplication()->enqueueMessage( JText::_( 'COM_SIMPLEFILEMANAGER_SENDMAIL_UNSUPPORTED_VISIBILITY_ERROR' ), 'warning' );
			} else {
				
				// Get recipients email from db
				$recipients = array();
				if($this->form["visibility"] == 3)
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery( true );
					$query->select( 'user_id' )
						->from( '#__simplefilemanager_user_documents' )
						->where( 'document_id = ' . $this->form["id"] );
					$db->setQuery( $query );
					$recipients = $db->loadColumn();
				} else if($this->form["visibility"] == 4) {
					$db    = JFactory::getDbo();
					$query = $db->getQuery( true );
					$query->select( 'group_id' )
						->from( '#__simplefilemanager_group_documents' )
						->where( 'document_id = ' . $this->form["id"] );
					$db->setQuery( $query );
					$group_ids = $db->loadColumn();

					foreach($group_ids as $g_id){
						$recipients = array_merge($recipients, JAccess::getUsersByGroup($g_id));
					}

					// Remove duplicates
					$recipients = array_unique($recipients, SORT_REGULAR);
				}
				
				if ( !empty( $recipients ) ) {

					$config  = JFactory::getConfig();
					$sender  = [ $config->get( 'mailfrom' ), $config->get( 'fromname' ) ];

					foreach ( $recipients as $recipient ) {

						$user = JFactory::getUser( (int) $recipient );
						
						// Message subject
						$subject = JText::_( 'COM_SIMPLEFILEMANAGER_EMAIL_SUBJ' );

						// Message body
						$body = JText::_( 'COM_SIMPLEFILEMANAGER_EMAIL_BODY' );
						$body = str_replace('{user_fullname}', $user->name, $body);
						$body = str_replace('{document_title}', $item->title, $body);
		
						// Send email
						$mailer = JFactory::getMailer();
						$mailer->setSender( $sender );
						$mailer->addRecipient( $user->email );
						$mailer->setSubject( $subject );
						$mailer->isHTML( true );
						$mailer->Encoding = 'base64';
						$mailer->setBody( $body );

						$send = $mailer->Send();

						if ( ! $send ) {
							JFactory::getApplication()->enqueueMessage( JText::_( 'JERROR' ) . ": " . $send->__toString(), 'error' );
						}
					}
				}
			}

			return true;
		}

		parent::postSaveHook($model, $validData);
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
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
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
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

	/**
	 * Method to run batch operations.
	 *
	 * @param   string  $model  The model
	 *
	 * @return  boolean  True on success.
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Document', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_simplefilemanager&view=documents' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}
}
