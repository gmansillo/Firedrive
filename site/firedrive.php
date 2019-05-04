<?php

/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @copyright   Firedrive
 */
defined('_JEXEC') or die;

JLoader::register('FiredriveHelperRoute', JPATH_COMPONENT . '/helpers/route.php');
JLoader::register('FiredriveHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/firedrive.php');

$app     = JFactory::getApplication();
$input   = $app->input;

if ($input->get('view') === 'category' && $input->get('layout') === 'modal')
{
	if (!JFactory::getUser()->authorise('core.create', 'com_firedrive'))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');

		return;
	}

	JFactory::getLanguage()->load('com_firedrive', JPATH_ADMINISTRATOR);
}

$controller = JControllerLegacy::getInstance('Firedrive');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();