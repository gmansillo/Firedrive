<?php
/**
 * @package     Simple File Manager
 * @author    Giovanni Mansillo
 *
 * @copyright   Copyright (C) 2005 - 2014 Giovanni Mansillo. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();


class com_simplefilemanagerInstallerScript
{

    function install($parent)
    {
        $this->createHtaccess();
        echo '<p>' . JText::_('COM_SIMPLEFILEMANAGER_INSTALL_TEXT') . '</p>';
        // $parent->getParent()->setRedirectURL('index.php?option=com_simplefilemanager');
    }

    function createHtaccess()
    {
        jimport('joomla.filesystem.file');

        $filePath = JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "uploads";

        $controlfile = $filePath . '/.htaccess';
        $controlfileContent = "Deny from all\r\nOptions None\r\nOptions +FollowSymLinks\r\n";

        if (!JFolder::exists($filePath))
        {
            JFolder::create($filePath);
        }

        if (!JFile::exists($controlfile))
        {
            ;
        }
        JFile::write($controlfile, $controlfileContent);
    }

    function uninstall($parent)
    {
        echo '<p>' . JText::_('COM_SIMPLEFILEMANAGER_UNINSTALL_TEXT') . '</p>';
    }

    function update($parent)
    {
        $this->createHtaccess();
        echo '<p>' . JText::_('COM_SIMPLEFILEMANAGER_UPDATE_TEXT') . '</p>';
    }

    function preflight($type, $parent)
    {
        // echo '<p>' . JText::_('COM_SIMPLEFILEMANAGER_PREFLIGHT_' . $type . '_TEXT') . '</p>';
    }

    function postflight($type, $parent)
    {
        // echo '<p>' . JText::_('COM_SIMPLEFILEMANAGER_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
    }
}