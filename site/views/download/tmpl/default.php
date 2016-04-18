<?php

	// no direct access
	defined('_JEXEC') or die;
	
	//Import filesystem libraries
	jimport('joomla.filesystem.file');
		
	// File stream
	ob_end_clean();
    JFactory::getApplication()->clearHeaders();
    JFactory::getApplication()->setHeader('Pragma', 'public', true);
    JFactory::getApplication()->setHeader('Expires', '0', true);
    JFactory::getApplication()->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0, private', true);
    JFactory::getApplication()->setHeader('Content-Type', 'application/octet-stream', true);
	if($this->params->get('force_download'))
        JFactory::getApplication()->setHeader('Content-Disposition', 'attachment; filename="'.basename($this->file_name).'";', true);
	else
        JFactory::getApplication()->setHeader('Content-Disposition', 'inline; filename="'.basename($this->file_name).'";', true);
    JFactory::getApplication()->setHeader('Content-Transfer-Encoding', 'binary', true);
    JFactory::getApplication()->setHeader('Content-Length', filesize($this->file_name), true);
    JFactory::getApplication()->sendHeaders();

	echo JFile::read($this->file_name);
	
	exit;