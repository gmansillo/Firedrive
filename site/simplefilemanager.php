<?php
/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */

defined('_JEXEC') or die;

JLoader::register('SimplefilemanagerHelperRoute', JPATH_COMPONENT . '/helpers/route.php');
JLoader::register('SimplefilemanagerHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/simplefilemanager.php');

$input = JFactory::getApplication()->input;

if ($input->get('view') === 'category' && $input->get('layout') === 'modal')
{
	if (!JFactory::getUser()->authorise('core.create', 'com_simplefilemanager'))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');

		return;
	}

	JFactory::getLanguage()->load('com_simplefilemanager', JPATH_ADMINISTRATOR);
}

$controller = JControllerLegacy::getInstance('Simplefilemanager');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
