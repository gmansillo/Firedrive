<?php
/**
 *
 * @package     Simple File Manager
 * @author		Giovanni Mansillo
 *
 * @copyright   Copyright (C) 2005 - 2014 Giovanni Mansillo. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

abstract class JHtmlContentAdministrator
{
	public static function featured($value = 0, $i, $canChange = true)
	{
		JHtml::_('bootstrap.tooltip');

		// Array of image, task, title, action
		$states	= array(
			0	=> array('star-empty',	'simplefilemanagers.featured',	'COM_SIMPLEFILEMANAGER_UNFEATURED',	'COM_SIMPLEFILEMANAGER_TOGGLE_TO_FEATURE'),
			1	=> array('star',		'simplefilemanagers.unfeatured',	'COM_SIMPLEFILEMANAGER_FEATURED',		'COM_SIMPLEFILEMANAGER_TOGGLE_TO_UNFEATURE'),
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon	= $state[0];
		if ($canChange)
		{
			$html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" class="btn btn-micro hasTooltip' . ($value == 1 ? ' active' : '') . '" title="'.JText::_($state[3]).'"><i class="icon-'
					. $icon.'"></i></a>';
		}
		else
		{
			$html	= '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'.JText::_($state[2]).'"><i class="icon-'
					. $icon.'"></i></a>';
		}

		return $html;
	}
}