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

abstract class SimplefilemanagerHelperRoute
{
    protected static $lookup;

    public static function getSimplefilemanagerRoute($id, $catid, $language = 0)
    {
        $needles = array(
            'simplefilemanager' => array((int)$id)
        );

        //Create the link
        $link = 'index.php?option=com_simplefilemanager&view=simplefilemanager&id=' . $id;

        if ($catid > 1)
        {
            $categories = JCategories::getInstance('Simplefilemanager');
            $category   = $categories->get($catid);

            if ($category)
            {
                $needles['category']   = array_reverse($category->getPath());
                $needles['categories'] = $needles['category'];
                $link .= '&catid=' . $catid;
            }
        }

        if ($language && $language != "*" && JLanguageMultilang::isEnabled())
        {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('a.sef AS sef')
                ->select('a.lang_code AS lang_code')
                ->from('#__languages AS a');

            $db->setQuery($query);
            $langs = $db->loadObjectList();
            foreach ($langs as $lang)
            {
                if ($language == $lang->lang_code)
                {
                    $link .= '&lang=' . $lang->sef;
                    $needles['language'] = $language;
                }
            }
        }

        if ($item = self::_findItem($needles))
        {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = self::_findItem())
        {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    protected static function _findItem($needles = null)
    {
        $app      = JFactory::getApplication();
        $menus    = $app->getMenu('site');
        $language = isset($needles['language']) ? $needles['language'] : '*';

        // Prepare the reverse lookup array.
        if (!isset(self::$lookup[$language]))
        {
            self::$lookup[$language] = array();

            $component = JComponentHelper::getComponent('com_simplefilemanager');

            $attributes = array('component_id');
            $values     = array($component->id);

            if ($language != '*')
            {
                $attributes[] = 'language';
                $values[]     = array($needles['language'], '*');
            }

            $items = $menus->getItems($attributes, $values);

            if ($items)
            {
                foreach ($items as $item)
                {
                    if (isset($item->query) && isset($item->query['view']))
                    {
                        $view = $item->query['view'];
                        if (!isset(self::$lookup[$language][$view]))
                        {
                            self::$lookup[$language][$view] = array();
                        }
                        if (isset($item->query['id']))
                        {
                            if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
                            {
                                self::$lookup[$language][$view][$item->query['id']] = $item->id;
                            }
                        }
                    }
                }
            }
        }

        if ($needles)
        {
            foreach ($needles as $view => $ids)
            {
                if (isset(self::$lookup[$language][$view]))
                {
                    foreach ($ids as $id)
                    {
                        if (isset(self::$lookup[$language][$view][(int)$id]))
                        {
                            return self::$lookup[$language][$view][(int)$id];
                        }
                    }
                }
            }
        }

        $active = $menus->getActive();
        if ($active && ($active->language == '*' || !JLanguageMultilang::isEnabled()))
        {
            return $active->id;
        }

        // if not found, return language specific home link
        $default = $menus->getDefault($language);
        return !empty($default->id) ? $default->id : null;
    }

    public static function getFormRoute($id, $return = null)
    {
        // Create the link.
        if ($id)
        {
            $link = 'index.php?option=com_simplefilemanagers&task=simplefilemanager.edit&id=' . $id;
        }
        else
        {
            $link = 'index.php?option=com_simplefilemanagers&task=simplefilemanager.add&id=0';
        }

        if ($return)
        {
            $link .= '&return=' . $return;
        }

        return $link;
    }

    public static function getCategoryRoute($catid, $language = 0)
    {
        if ($catid instanceof JCategoryNode)
        {
            $id       = $catid->id;
            $category = $catid;
        }
        else
        {
            $id       = (int)$catid;
            $category = JCategories::getInstance('Simplefilemanager')->get($id);
        }

        if ($id < 1)
        {
            $link = '';
        }
        else
        {
            //Create the link
            $link    = 'index.php?option=com_simplefilemanager&view=category&id=' . $id;
            $needles = array(
                'category' => array($id)
            );

            if ($language && $language != "*" && JLanguageMultilang::isEnabled())
            {
                $db    = JFactory::getDbo();
                $query = $db->getQuery(true)
                    ->select('a.sef AS sef')
                    ->select('a.lang_code AS lang_code')
                    ->from('#__languages AS a');

                $db->setQuery($query);
                $langs = $db->loadObjectList();
                foreach ($langs as $lang)
                {
                    if ($language == $lang->lang_code)
                    {
                        $link .= '&lang=' . $lang->sef;
                        $needles['language'] = $language;
                    }
                }
            }

            if ($item = self::_findItem($needles))
            {
                $link .= '&Itemid=' . $item;
            }
            else
            {
                if ($category)
                {
                    $catids  = array_reverse($category->getPath());
                    $needles = array(
                        'category' => $catids,
                        'categories' => $catids
                    );
                    if ($item = self::_findItem($needles))
                    {
                        $link .= '&Itemid=' . $item;
                    }
                    elseif ($item = self::_findItem())
                    {
                        $link .= '&Itemid=' . $item;
                    }
                }
            }
        }

        return $link;
    }
}