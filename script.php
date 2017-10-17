<?php

/**
 * @package     com_simplefilemanager
 * @copyright   Copyright (C) 2015. Tutti i diritti riservati.
 * @license     GNU General Public License versione 2 o successiva; vedi LICENSE.txt
 * @author      Giovanni Mansillo
 */

defined('_JEXEC') or die();


class com_simplefilemanagerInstallerScript
{

	function install($parent)
	{
		// $parent->getParent()->setRedirectURL('index.php?option=com_simplefilemanager');
	}

	function uninstall($parent)
	{
	}

	function update($parent)
	{
	}

	function preflight($type, $parent)
	{
		$jversion = new JVersion();

		// Manifest file minimum Joomla version
		$minimum_joomla_release = $parent->get("manifest")->attributes()->version;

		// abort if the current Joomla release is older
		if (version_compare($jversion->getShortVersion(), $minimum_joomla_release, 'lt'))
		{
			$errorMessage = sprintf(JText::_('COM_SIMPLEFILEMANAGER_PREFLIGHT_VERSION_ERROR'), $minimum_joomla_release, $jversion->getShortVersion());
			Jerror::raiseWarning(null, $errorMessage);

			return false;
		}

		// echo '<p>' . JText::_('COM_SIMPLEFILEMANAGER_PREFLIGHT_' . $type . '_TEXT') . '</p>';
	}

	function postflight($type, $parent)
	{
	}
}