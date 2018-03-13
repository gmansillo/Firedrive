<?php

/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;

/**
 * Documents list controller class.
 */
class SimplefilemanagerControllerDocuments extends JControllerAdmin {

    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_SIMPLEFILEMANAGER_DOCUMENTS';

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JControllerLegacy
     * @since   1.6
     */
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JModelLegacy  The model.
     *
     * @since   1.6
     */
    public function getModel($name = 'Document', $prefix = 'SimplefilemanagerModel', $config = array('ignore_request' => true)) {
        return parent::getModel($name, $prefix, $config);
    }

}
