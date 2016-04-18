<?php

/**
 *
 * @package     Simple File Manager
 * @author		Giovanni Mansillo
 *
 * @copyright   Copyright (C) 2005 - 2014 Giovanni Mansillo. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 */

defined('_JEXEC') or die;

require_once (JPATH_COMPONENT_ADMINISTRATOR . '/helpers/simplefilemanager.php');

class SimplefilemanagerController extends JControllerLegacy
{
	protected $default_view = 'simplefilemanagers';

	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/simplefilemanager.php';

		JHtml::_('formbehavior.chosen', 'select');
		$view   = $this->input->get('view', 'simplefilemanagers');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		if ($view == 'simplefilemanager' && $layout == 'edit' && !$this->checkEditId('com_simplefilemanager.edit.simplefilemanager', $id))
		{
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanagers', false));

			return false;
		}

		parent::display();

		return $this;
	}

	public function download()
	{

		$jinput = JFactory::getApplication()->input;
		$id = $jinput->get->get('id',0, 'INTEGER');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('file_name');
		$query->from($db->quoteName('#__simplefilemanager'));
		$query->where($db->quoteName('id') . ' = '.$id);
		$db->setQuery($query);
		$row = $db->loadRow();

		if(!$row) die("No input file");

		// Adjusting file path for backend view
		if(!file_exists ($row['0'])) die("File not found");

		header("Content-Transfer-Encoding: binary");
		header("Content-type: application/octet-stream");
		header('Content-Disposition: attachment; filename="'.basename($row['0']).'"');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Cache-Control: private');
		header('Content-Length: '.filesize($row['0']));
		ob_clean();  flush();
		readfile($row['0']);
		exit;

	}

	public function massiveImport()
	{
		$jinput = JFactory::getApplication()->input;
		$files = $jinput->get('item', array(), 'ARRAY');
		$param = JComponentHelper::getParams('com_simplefilemanager');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.utilities.date');
		define("DS", DIRECTORY_SEPARATOR);

		$uploadedSuccessfullCount = 0;

		foreach($files as $file)
		{

		    $src = JPATH_ROOT.DS."sfmimporter".DS.$file;

		    // Check file extension.
	        if (SimplefilemanagerHelper::hasSafeExtension($file))
	        {
	            // File upload.
	            $upload = SimplefilemanagerHelper::moveFile($src, $file);
	            if ($upload) {
	                $file_name = $upload;
	                $md5hash = md5_file($file);
	                $file_size = filesize($file);
	                $title = JFile::stripExt($file);
	                $author = JFactory::getUser()->id;
	                $date = & JFactory::getDate('now');

	                // Save to db.
	                $db = JFactory::getDbo();
	                $query = $db->getQuery(true);
	                $columns = array('state', 'title', 'author', 'file_created', 'file_name','file_size',md5hash);
	                $values = array(0, $db->quote($title), $db->quote($author), $db->quote($date->format('Y-m-d H:m:s',false)), $db->quote($file_name),$db->quote($file_size),$db->quote($md5hash));

	                $query
    	                ->insert($db->quoteName('#__simplefilemanager'))
    	                ->columns($db->quoteName($columns))
    	                ->values(implode(',', $values));

	                $db->setQuery($query);
	                $db->execute();

	                $uploadedSuccessfullCount++;

	            }
	        }
		}

		if($uploadedSuccessfullCount>0)
			JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_FIELD_UPLOADED_SUCCESSFULLY_MSG',$uploadedSuccessfullCount), 'message');
		return $this->display();
	}

}