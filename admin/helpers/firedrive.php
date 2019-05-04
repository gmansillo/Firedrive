<?php

/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @copyright   Firedrive
 */
defined('_JEXEC') or die;

/**
 * Documents component helper.
 * @since   5.2.1
 */
class FiredriveHelper extends JHelperContent
{

	/**
	 * Detects max size of file cab be uploaded to server
	 *
	 * Based on php.ini parameters "upload_max_filesize", "post_max_size" &
	 * "memory_limit".
	 *
	 * @author  Paul Melekhov, edited by lostinscope
	 * @return int Max file size in bytes
	 * @since   5.2.1
	 */
	public static function detectMaxUploadFileSize()
	{
		/**
		 * Converts shorthands like "2M" or "512K" to bytes
		 *
		 * @param $size
		 *
		 * @return mixed
		 * @since   5.2.1
		 */
		$normalize = function ($size) {
			if (preg_match("/^([\d\.]+)([KMG])$/i", $size, $match))
			{
				$pos = array_search($match[2], array("K", "M", "G"));
				if ($pos !== false)
				{
					$size = $match[1] * pow(1024, $pos + 1);
				}
			}

			return $size;
		};

		$max_upload   = $normalize(ini_get("upload_max_filesize"));
		$max_post     = (ini_get("post_max_size") == 0) ?
			function () {
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
	 * @param  stdClass $document .
	 *
	 * @return  void
	 * @since   5.2.1
	 */
	public static function processDocumentIcon(&$document)
	{
		if (is_null($document)) return;
		if (!empty($document->icon)) return;    // Custom icon already set

		$params       = JComponentHelper::getParams('com_firedrive');
		$default_icon = JURI::root() . "/media/com_firedrive/images/document.svg";

		if (!$params->get('use_smart_icons', 1))
		{ // Smart icon disabled
			$document->icon = $default_icon;

			return;
		}

		$file_extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
		$smarticons_dir = "/media/com_firedrive/smarticons/";

		if (file_exists(JPATH_ROOT . $smarticons_dir . $file_extension . ".svg"))
			$document->icon = JURI::root() . $smarticons_dir . $file_extension . ".svg";
		else if (file_exists(JPATH_ROOT . $smarticons_dir . $file_extension . ".png"))
			$document->icon = JURI::root() . $smarticons_dir . $file_extension . ".png";
		else if (file_exists(JPATH_ROOT . $smarticons_dir . $file_extension . ".gif"))
			$document->icon = JURI::root() . $smarticons_dir . $file_extension . ".gif";
		else if (file_exists(JPATH_ROOT . $smarticons_dir . $file_extension . ".jpg"))
			$document->icon = JURI::root() . $smarticons_dir . $file_extension . ".jpg";
		else
			$document->icon = $default_icon;

		return;
	}

	/**
	 * Convert size in bytes to a readable format.
	 *
	 * @param   string $size The size to format in byte.
	 *
	 * @return  string
	 * @since   5.2.1
	 */
	public static function convertToReadableSize($size)
	{

		if (!($size > 0))
			return "0 KB";

		$base   = log($size) / log(1024);
		$suffix = array("Byte", "KB", "MB", "GB", "TB");
		$f_base = floor($base);

		return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
	}

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string $vName The name of the active view.
	 *
	 * @return  void
	 * @since   5.2.1
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_FIREDRIVE_SUBMENU_DOCUMENTS'), 'index.php?option=com_firedrive', $vName == 'documents'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_FIREDRIVE_SUBMENU_CATEGORIES'), 'index.php?option=com_categories&extension=com_firedrive', $vName == 'categories'
		);
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   stdClass[] &$items The document category objects
	 *
	 * @return  stdClass[]
	 * @since   5.2.1
	 */
	public static function countItems(&$items)
	{
		$db = JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed     = 0;
			$item->count_archived    = 0;
			$item->count_unpublished = 0;
			$item->count_published   = 0;
			$query                   = $db->getQuery(true);
			$query->select('state, COUNT(*) AS count')
				->from($db->qn('#__firedrive'))
				->where('catid = ' . (int) $item->id)
				->group('state');
			$db->setQuery($query);
			$documents = $db->loadObjectList();

			foreach ($documents as $document)
			{
				if ($document->state == 1)
				{
					$item->count_published = $document->count;
				}

				if ($document->state == 0)
				{
					$item->count_unpublished = $document->count;
				}

				if ($document->state == 2)
				{
					$item->count_archived = $document->count;
				}

				if ($document->state == -2)
				{
					$item->count_trashed = $document->count;
				}
			}
		}

		return $items;
	}

	// TODO: Remove this method from here and place it in single local scopes

	/**
	 * Copy Simple files in a new folder.
	 *
	 * @param string $source Path of the uploaded file on the server
	 *
	 * @return string|boolean Copied file path (in case of success) or false (in case of error)
	 * @since   5.2.1
	 */
	public static function copyFile($source)
	{
		jimport('joomla.filesystem.file');

		$fileName   = pathinfo($source, PATHINFO_BASENAME);
		$destFolder = JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . uniqid("", true) . DIRECTORY_SEPARATOR;

		mkdir($destFolder);

		return JFile::copy($source, $destFolder . $fileName) ? $destFolder . $fileName : false;
	}

	public static function getFdkey()
	{
		$app = JFactory::getApplication();

		if ($app->isClient('site'))
			return JText::_('COM_FIREDRIVE_CREDITS');
		else
			return JText::_('COM_FIREDRIVE_UPGRADE_INSTRUCTIONS');
	}
}
