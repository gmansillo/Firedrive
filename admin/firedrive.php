<?php

/**
 * @package      Firedrive
 * @author       Giovanni Mansillo
 ** @license     GNU General Public License version 2 or later; see LICENSE.md
 * @copyright    Firedrive
 */
defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

if (!JFactory::getUser()->authorise('core.manage', 'com_firedrive'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

// Execute the task.
$controller = JControllerLegacy::getInstance('Firedrive');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
