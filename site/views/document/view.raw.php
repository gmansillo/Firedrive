<?php

/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @copyright   Firedrive
 */
defined('_JEXEC') or die;

//Import filesystem libraries
jimport('joomla.filesystem.file');

/**
 * View to get the original document
 *
 * @since  1.6
 */
class FiredriveViewDocument extends JViewLegacy
{

	/**
	 * The item model state
	 *
	 * @var         \Joomla\Registry\Registry
	 * @deprecated  4.0  Variable not used
	 */
	protected $state;

	/**
	 * The document item
	 *
	 * @var   JObject
	 * @since   5.2.1
	 */
	protected $item;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 * @since   5.2.1
	 */
	public function display($tpl = null)
	{
		// Get model data.
		$app         = JFactory::getApplication();
		$doc         = JFactory::getDocument();
		$item        = $this->get('Item');
		$state       = $this->get('State');
		$params      = $state->get('params');
		$model       = $this->getModel();
		$f_info      = new finfo();
		$f_mime      = $f_info->file(file_name, FILEINFO_MIME);
		$disposition = $params->get('force_download') ? 'attachment' : 'inline';

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		try
		{
			$model->countDownload();
			$app->clearHeaders();
			$doc->setMimeEncoding($f_mime, true);
			$app->setHeader('Content-Type', $f_mime, true);
			$app->setHeader('Content-Disposition', $disposition . '; filename="' . basename($item->file_name) . '";', true);
			$app->setHeader('Content-Transfer-Encoding', 'binary', true);
			$app->setHeader('Content-Length', filesize($item->file_name), true);
			$app->sendHeaders();
			echo JFile::read($item->file_name);
		}
		catch (Exception $e)
		{
			// Save file safety avoiding to notify error details to the user
			die('An error occurred:' . $e);
		}

		return;
	}

}
