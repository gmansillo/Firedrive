<?php

/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;

JLoader::register('FiredriveHelper', JPATH_ADMINISTRATOR . '/components/com_firedrive/helpers/firedrive.php');

/**
 * View to edit a document.
 */
class FiredriveViewDocument extends JViewLegacy {

    /**
     * The JForm object
     *
     * @var  JForm
     */
    protected $form;

    /**
     * The active item
     *
     * @var  object
     */
    protected $item;

    /**
     * The model state
     *
     * @var  object
     */
    protected $state;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null) {
        // Initialiase variables.
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');
        $this->isNew = ($this->item->id == 0);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        return parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolbar() {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user       = JFactory::getUser();
        $userId     = $user->id;
        $isNew      = ($this->item->id == 0);
        $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

        // Since we don't track these assets at the item level, use the category id.
        $canDo = JHelperContent::getActions('com_firedrive', 'category', $this->item->catid);

        JToolbarHelper::title($isNew ? JText::_('COM_FIREDRIVE_MANAGER_DOCUMENT_NEW') : JText::_('COM_FIREDRIVE_MANAGER_DOCUMENT_EDIT'), 'pencil-2 document');

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit') || count($user->getAuthorisedCategories('com_firedrive', 'core.create')) > 0)) {
            JToolbarHelper::apply('document.apply');
            JToolbarHelper::save('document.save');

            if ($canDo->get('core.create')) {
                JToolbarHelper::save2new('document.save2new');
            }
        }

        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            JToolbarHelper::save2copy('document.save2copy');
        }

        if (empty($this->item->id)) {
            JToolbarHelper::cancel('document.cancel');
        } else {
            // if (JComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $canDo->get('core.edit'))
            // {
            // 	JToolbarHelper::versions('com_firedrive.document', $this->item->id);
            // }

            JToolbarHelper::cancel('document.cancel', 'JTOOLBAR_CLOSE');
        }

        JToolbarHelper::divider();
    }

}
