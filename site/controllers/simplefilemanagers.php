<?php
/**
 * @version     1.0.0
 * @package     com_simplefilemanager
 * @copyright   
 * @license     
 * @author       <> - 
 */

// No direct access.
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/controller.php';

/**
 * Simplefilemanagers list controller class.
 */
class SimplefilemanagerControllerSimplefilemanagers extends SimplefilemanagerController
{
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'Simplefilemanagers', $prefix = 'SimplefilemanagerModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}