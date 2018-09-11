<?php

/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die();

class com_firedriveInstallerScript {

    function preflight($type, $parent) {
        $jversion = new JVersion();
        $minimum_joomla_release = $parent->get("manifest")->attributes()->version;
        $current_simple_version = $parent->get("manifest")->version;

        // Abort if the current Joomla release is older
        if (version_compare($jversion->getShortVersion(), $minimum_joomla_release, 'lt')) {
            $errorMessage = sprintf(JText::_('COM_FIREDRIVE_PREFLIGHT_VERSION_ERROR'), $current_simple_version, $minimum_joomla_release, $jversion->getShortVersion());
            JFactory::getApplication()->enqueueMessage($errorMessage, 'warning');
            return false;
        }
    }

    /**
     * $parent is the class calling this method.
     * install runs after the database scripts are executed.
     * If the extension is new, the install method is run.
     * If install returns false, Joomla will abort the install and undo everything already done.
     */
    function install($parent) {
        echo JText::_('COM_FIREDRIVE_INSTALL_TEXT');
        // $parent->getParent()->setRedirectURL('index.php?option=com_firedrive');
    }

    /**
     * $parent is the class calling this method
     * uninstall runs before any other action is taken (file removal or database processing).
     */
    function uninstall($parent) {
        
    }

}
