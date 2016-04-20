<?php

/**
 *
 * @package Simple File Manager
 * @author Giovanni Mansillo
 *
 * @copyright Copyright (C) 2005 - 2014 Giovanni Mansillo. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class SimplefilemanagerHelper
{

    public static function getActions($categoryId = 0)
    {
        $user      = JFactory::getUser();
        $result = new JObject();

        if (empty($categoryId))
        {
            $assetName = 'com_simplefilemanager';
            $level = 'component';
        }
        else
        {
            $assetName = 'com_simplefilemanager.category.' . (int)$categoryId;
            $level     = 'category';
        }

        $actions = JAccess::getActions('com_simplefilemanager', $level);

        foreach ($actions as $action)
        {
            $result->set($action->name, $user->authorise($action->name, $assetName));
        }

        return $result;
    }

    public static function addSubmenu($vName = 'simplefilemanagers')
    {
        JHtmlSidebar::addEntry(JText::_('COM_SIMPLEFILEMANAGER_SUBMENU_SIMPLEFILEMANAGERS'), 'index.php?option=com_simplefilemanager&view=simplefilemanagers', $vName == 'simplefilemanagers');
        JHtmlSidebar::addEntry(JText::_('COM_SIMPLEFILEMANAGER_SUBMENU_CATEGORIES'), 'index.php?option=com_categories&extension=com_simplefilemanager', $vName == 'categories');
        if ($vName == 'categories')
        {
            JToolbarHelper::title(JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', JText::_('com_simplefilemanager')), 'simplefilemanagers-categories');
        }
        JHtmlSidebar::addEntry(JText::_('COM_SIMPLEFILEMANAGER_SUBMENU_SUMMARY'), 'index.php?option=com_simplefilemanager&view=summary', $vName == 'summary');
    }

    /**
     * Check if a file has a dangerous extension
     *
     * @param string $filename
     *            Name of file to be checked
     */
    public static function hasSafeExtension($filename)
    {
        jimport('joomla.filesystem.file');

        $app         = &JFactory::getApplication();
        $params      = JComponentHelper::getParams('com_simplefilemanager');
        $forbiddenExtensions = $params->get('forbiddenExtensions');
        $forbiddenExtensions = preg_replace(" ", "", $forbiddenExtensions);
        $dangExtList = explode(",", $forbiddenExtensions);

        $ext = strtolower(JFile::getExt($filename));
        if (in_array($ext, $dangExtList))
        {
            return false;
        }

        return true;
    }

    /**
     * Upload Simple File Manager files in the right folder.
     *
     * @param string $tmp_name
     *            Temporary path of the uploaded file on the server
     * @param string $file_name
     *            Name of the uploaded file
     * @return uploaded file path (in case of success) or false (in case of error)
     */
    public static function uploadFile($tmp_name, $file_name)
    {
        jimport('joomla.filesystem.file');

        $src = $tmp_name;
        $dest = JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . uniqid("", true) . DIRECTORY_SEPARATOR . JFile::makeSafe(JFile::getName($file_name));

        if (!JFile::upload($src, $dest))
        {
            return false;
        }
        else
        {
            return $dest;
        }
    }

    /**
     * Delete a document from the filesystem
     *
     * @param string $filename
     *            Name of file to be deleted
     * @return boolean true on success
     */
    public static function deleteFile($filename)
    {
        jimport('joomla.filesystem.file');

        return JFile::delete($filename);
    }

    /**
     * Send and email to notify a new file upload
     *
     * @param string $dest
     * @return boolean true in case of success
     */
    public static function sendMail(&$form)
    {
        // Check requisites for email sending
        if ($form["state"] != 1)
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_SENDMAIL_UNPUBLISHED_DOCUMENT_ERROR'), 'warning');
            return false;
        }
        elseif ($form["visibility"] != 3)
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_SENDMAIL_UNSUPPORTED_VISIBILITY_ERROR'), 'warning');
            return false;
        }
        elseif ($form["reserved_user"] <= 0)
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_SENDMAIL_NO_RECIPIENT_SPECIFIED_ERROR'), 'warning');
            return false;
        }

        // Load objects
        $mailer = JFactory::getMailer();
        $config = JFactory::getConfig();

        // Mail sender
        $sender = array($config->get('mailfrom'), $config->get('fromname'));
        $mailer->setSender($sender);

        // Mail recipient
        $user = JFactory::getUser((int)$form["reserved_user"]);
        if ($user->guest)
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_SENDMAIL_NO_RECIPIENT_SPECIFIED_ERROR'), 'warning');
        }
        $recipient = $user->email;
        $mailer->addRecipient($recipient);

        // Mail contents
        $mailer->setSubject(JText::_('COM_SIMPLEFILEMANAGER_EMAIL_SUBJ'));
        $body = JText::_('COM_SIMPLEFILEMANAGER_EMAIL_BODY');
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $mailer->setBody($body);

        $send = $mailer->Send();

        if (!$send)
        {
            return JFactory::getApplication()->enqueueMessage(JText::_('JERROR') . ": " . $send->__toString(), 'error');
        }

        return true;
    }

    public static function getMaxFileUploadSize()
    {
        return min(
            SimplefilemanagerHelper::return_bytes(ini_get('post_max_size')),
            SimplefilemanagerHelper::return_bytes(ini_get('upload_max_filesize'))
        );
    }

    private static function return_bytes($size_str)
    {
        switch (substr($size_str, -1))
        {
            case 'M':
            case 'm':
                return (int)$size_str * 1048576;
            case 'K':
            case 'k':
                return (int)$size_str * 1024;
            case 'G':
            case 'g':
                return (int)$size_str * 1073741824;
            default:
                return $size_str;
        }
    }
}