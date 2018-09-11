<?php

/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;

/**
 * Firedrive Component Category Tree
 */
class FiredriveCategories extends JCategories {

    /**
     * Class constructor
     *
     * @param   array  $options  Array of options
     */
    public function __construct($options = array()) {
        $options['table']      = '#__firedrive';
        $options['extension']  = 'com_firedrive';
        $options['statefield'] = 'state';
        parent::__construct($options);
    }

}
