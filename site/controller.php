<?php

/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;

/**
 * Simplefilemanager Component Controller
 */
class SimplefilemanagerController extends JControllerLegacy {

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *                          Recognized key values include 'name', 'default_task', 'model_path', and
     *                          'view_path' (this list is not meant to be comprehensive).
     */
    public function __construct($config = array()) {
        $this->input = JFactory::getApplication()->input;

        // Simplefilemanager frontpage Editor category proxying:
        if ($this->input->get('view') === 'category' && $this->input->get('layout') === 'modal') {
            JHtml::_('stylesheet', 'system/adminlist.css', array(), true);
            $config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
        }

        parent::__construct($config);
    }

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return  JControllerLegacy  This object to support chaining.
     */
    public function display($cachable = false, $urlparams = array()) {

        // Set the default view name and format from the Request.
        $vName = $this->input->get('view', 'categories');
        $this->input->set('view', $vName);

        $safeurlparams = array('catid'            => 'INT', 'id'               => 'INT', 'cid'              => 'ARRAY', 'year'             => 'INT', 'month'            => 'INT',
            'limit'            => 'UINT', 'limitstart'       => 'UINT', 'showall'          => 'INT', 'return'           => 'BASE64', 'filter'           => 'STRING',
            'filter_order'     => 'CMD', 'filter_order_Dir' => 'CMD', 'filter-search'    => 'STRING', 'print'            => 'BOOLEAN',
            'lang'             => 'CMD');

        parent::display($cachable, $safeurlparams);

        return $this;
    }

}
