<?php

/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;

/**
 * Documents component helper.
 */
class SimplefilemanagerHelper extends JHelperContent {

    /**
     * Detects max size of file cab be uploaded to server
     *
     * Based on php.ini parameters "upload_max_filesize", "post_max_size" &
     * "memory_limit". 
     *
     * @author Paul Melekhov, edited by lostinscope
     * @return int Max file size in bytes
     */
    public static function detectMaxUploadFileSize() {
        /**
         * Converts shorthands like "2M" or "512K" to bytes
         *
         * @param $size
         * @return mixed
         */
        $normalize = function($size) {
            if (preg_match("/^([\d\.]+)([KMG])$/i", $size, $match)) {
                $pos = array_search($match[2], array("K", "M", "G"));
                if ($pos !== false) {
                    $size = $match[1] * pow(1024, $pos + 1);
                }
            }
            return $size;
        };
        $max_upload = $normalize(ini_get("upload_max_filesize"));

        $max_post = (ini_get("post_max_size") == 0) ?
                function() {
            throw new Exception("Check Your php.ini settings");
        } : $normalize(ini_get("post_max_size"));

        $memory_limit = (ini_get("memory_limit") == -1) ?
                $max_post : $normalize(ini_get("memory_limit"));

        if ($memory_limit < $max_post || $memory_limit < $max_upload)
            return $memory_limit;

        if ($max_post < $max_upload)
            return $max_post;

        $maxFileSize = min($max_upload, $max_post, $memory_limit);
        return $maxFileSize;
    }

    /**
     * Update document icon in according with global params.
     *
     * @param  stdClass $document.
     *
     * @return  void
     */
    public static function processDocumentIcon(&$document) {

        $params = JComponentHelper::getParams('com_simplefilemanager');

        if (!is_null($document) && empty($document->icon)) {
            $smartIconsDirectory = JPATH_ROOT . "/media/com_simplefilemanager/smartIcons/";
            $smartIconsUri       = JURI::root() . "/media/com_simplefilemanager/smartIcons/";
            $availableSmartIcons = array_map('basename', glob($smartIconsDirectory . "*.png", GLOB_BRACE));
            $neededSmartIcon     = pathinfo($document->file_name, PATHINFO_EXTENSION) . ".png";
            if ($params->get('use_smart_icons', 1) && in_array($neededSmartIcon, $availableSmartIcons)) {
                $document->icon = $smartIconsUri . $neededSmartIcon;
            } else {
                $document->icon = JURI::root() . "/media/com_simplefilemanager/images/document.png";
            }
        }

        return;
    }

    /**
     * Convert size in bytes to a readable format.
     *
     * @param   string  $size  The size to format in byte.
     *
     * @return  string
     */
    public static function convertToReadableSize($size) {

        if (!($size > 0))
            return "0 KB";

        $base   = log($size) / log(1024);
        $suffix = array("", "KB", "MB", "GB", "TB");
        $f_base = floor($base);

        return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
    }

    /**
     * Show upgrade instructions in a notice.
     *
     * @return  void
     */
    public static function showUpgradeNotice() {
        $message_type = "notice";
        $app          = JFactory::getApplication();
        $app->enqueueMessage(JText::_('COM_SIMPLEFILEMANAGER_UPGRADE_INSTRUCTIONS'), $message_type);
    }

    /**
     * Configure the Linkbar.
     *
     * @param   string  $vName  The name of the active view.
     *
     * @return  void
     */
    public static function addSubmenu($vName) {
        JHtmlSidebar::addEntry(
                JText::_('COM_SIMPLEFILEMANAGER_SUBMENU_DOCUMENTS'), 'index.php?option=com_simplefilemanager', $vName == 'documents'
        );

        JHtmlSidebar::addEntry(
                JText::_('COM_SIMPLEFILEMANAGER_SUBMENU_CATEGORIES'), 'index.php?option=com_categories&extension=com_simplefilemanager', $vName == 'categories'
        );
    }

    /**
     * Adds Count Items for Category Manager.
     *
     * @param   stdClass[]  &$items  The document category objects
     *
     * @return  stdClass[]
     */
    public static function countItems(&$items) {
        $db = JFactory::getDbo();

        foreach ($items as $item) {
            $item->count_trashed     = 0;
            $item->count_archived    = 0;
            $item->count_unpublished = 0;
            $item->count_published   = 0;
            $query                   = $db->getQuery(true);
            $query->select('state, count(*) AS count')
                    ->from($db->qn('#__simplefilemanager'))
                    ->where('catid = ' . (int) $item->id)
                    ->group('state');
            $db->setQuery($query);
            $documents               = $db->loadObjectList();

            foreach ($documents as $document) {
                if ($document->state == 1) {
                    $item->count_published = $document->count;
                }

                if ($document->state == 0) {
                    $item->count_unpublished = $document->count;
                }

                if ($document->state == 2) {
                    $item->count_archived = $document->count;
                }

                if ($document->state == -2) {
                    $item->count_trashed = $document->count;
                }
            }
        }

        return $items;
    }

}
