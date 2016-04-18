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

class SimplefilemanagerControllerSimplefilemanagers extends JControllerAdmin
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unfeatured',	'featured');
	}

	public function featured()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user   = JFactory::getUser();
		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('featured' => 1, 'unfeatured' => 0);
		$task   = $this->getTask();
		$value  = JArrayHelper::getValue($values, $task, 0, 'int');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.edit.state', 'com_simplefilemanager.simplefilemanager.'.(int) $id))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				JError::raiseNotice(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			}
		}

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Publish the items.
			if (!$model->featured($ids, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_simplefilemanager&view=simplefilemanagers');
	}

	public function getModel($name = 'Simplefilemanager', $prefix = 'SimplefilemanagerModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	public function saveOrderAjax()
	{
		$input = JFactory::getApplication()->input;
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		$model = $this->getModel();

		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		JFactory::getApplication()->close();
	}

}