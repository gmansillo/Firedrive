<?php

/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class SimplefilemanagerViewDocumentform extends JViewLegacy {

    protected $state;
    protected $item;
    protected $form;
    protected $params;

    /**
     * Display the view
     */
    public function display($tpl = null) {

        $this->state  = $this->get('State');
        $this->item   = $this->get('Data');
        $this->params = $this->state->get('params');
        $this->form   = $this->get('Form');

        $temp = clone $this->item->params;

        $active = $app->getMenu()->getActive();

        if ($active && strpos($active->link, 'view=documentform')) {
            // If the current view is the active item and a document view for this document, then the menu item params take priority
            // $item->params are the document params, $temp are the menu item params
            // Merge so that the menu item params take priority
            $this->item->params->merge($temp);
        } else {
            // Merge so that document params take priority
            $temp->merge($this->item->params);
            $this->item->params = $temp;
        }


        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document
     */
    protected function _prepareDocument() {

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }

}
