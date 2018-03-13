<?php

/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;

/**
 * Simplefilemanager Component Category Tree
 */
class SimplefilemanagerCategories extends JCategories {

    /**
     * Class constructor
     *
     * @param   array  $options  Array of options
     */
    public function __construct($options = array()) {
        $options['table']      = '#__simplefilemanager';
        $options['extension']  = 'com_simplefilemanager';
        $options['statefield'] = 'state';
        parent::__construct($options);
    }

}
