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

jimport('joomla.log.log');

JLog::addLogger(
    array(
        'text_file' => 'com_simplefilemanager.info.php',
        'text_entry_format' => '{DATETIME} {PRIORITY} - {MESSAGE}'
    ),
    JLog::ALL & ~JLog::DEBUG,
    array('com_simplefilemanager')
);
JLog::addLogger(
    array(
        'text_file' => 'com_simplefilemanager.debug.php',
        'text_entry_format' => '{DATETIME} {PRIORITY} - {MESSAGE}'
    ),
    JLog::ALL,
    array('com_simplefilemanager')
);

if (!JFactory::getUser()->authorise('core.manage', 'com_simplefilemanager'))
{
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$controller = JControllerLegacy::getInstance('Simplefilemanager');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();