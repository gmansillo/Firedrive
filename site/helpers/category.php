<?php
/**
 *
 * @package     Simple File Manager
 * @author        Giovanni Mansillo
 *
 * @copyright   Copyright (C) 2005 - 2014 Giovanni Mansillo. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class SimplefilemanagerCategories extends JCategories
{
    public function __construct($options = array())
    {
        $options['table']     = '#__simplefilemanager';
        $options['extension'] = 'com_simplefilemanager';
        parent::__construct($options);
    }
}