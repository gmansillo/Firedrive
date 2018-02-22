<?php

/**
 * @package     Simple
 * @copyright   Copyright (C) 2015. Tutti i diritti riservati.
 * @license     GNU General Public License versione 2 o successiva; vedi LICENSE.txt
 * @author      Giovanni Mansillo
 */

defined('_JEXEC') or die;

abstract class JHtmlContentAdministrator
{
	// TODO: Add the feetured funtionality
	public static function featured($value = 0, $i, $canChange = true)
	{
		JHtml::_('bootstrap.tooltip');

		// Array of image, task, title, action
		$states = array(
			0 => array('star-empty', 'documents.featured', 'COM_SIMPLEFILEMANAGER_UNFEATURED', 'COM_SIMPLEFILEMANAGER_TOGGLE_TO_FEATURE'),
			1 => array('star', 'documents.unfeatured', 'COM_SIMPLEFILEMANAGER_FEATURED', 'COM_SIMPLEFILEMANAGER_TOGGLE_TO_UNFEATURE'),
		);
		$state  = JArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon   = $state[0];
		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn button btn-micro hasTooltip' . ($value == 1 ? ' active' : '') . '" title="' . JText::_($state[3]) . '"><i class="icon-'
				. $icon . '"></i></a>';
		}
		else
		{
			$html = '<a class="btn button btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="' . JText::_($state[2]) . '"><i class="icon-'
				. $icon . '"></i></a>';
		}

		return $html;
	}
}