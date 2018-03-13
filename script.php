<?php

/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die();

class com_simplefilemanagerInstallerScript {

    function install($parent) {
        echo JText::_('COM_SIMPLEFILEMANAGER_INSTALL_TEXT');
        // $parent->getParent()->setRedirectURL('index.php?option=com_simplefilemanager');
    }

    function preflight($type, $parent) {
        $jversion = new JVersion();

        // Manifest file minimum Joomla version
        $minimum_joomla_release = $parent->get("manifest")->attributes()->version;
        $current_simple_version = $parent->get("manifest")->version;

        // abort if the current Joomla release is older
        if (version_compare($jversion->getShortVersion(), $minimum_joomla_release, 'lt')) {
            $errorMessage = sprintf(JText::_('COM_SIMPLEFILEMANAGER_PREFLIGHT_VERSION_ERROR'), $current_simple_version, $minimum_joomla_release, $jversion->getShortVersion());
            Jerror::raiseWarning(null, $errorMessage);

            return false;
        }
    }

}
