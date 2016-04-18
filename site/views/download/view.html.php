<?php

// No direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class SimplefilemanagerViewDownload extends JViewLegacy
{

    protected $state;

    protected $item;

    protected $form;

    protected $params;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        if (! empty($this->item)) {

            $this->form = $this->get('Form');
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        // Get API
        $jinput = JFactory::getApplication()->input;
        $user = JFactory::getUser();
        $this->params = JComponentHelper::getParams('com_simplefilemanager');

        $id = $jinput->getInt('id', 0);
        // Changed from:
        // $id = $jinput->get->get('id',0, 'INTEGER');
        // Because of incompatibility with router.php

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('file_name, visibility, reserved_group, reserved_user, state');
        $query->from($db->quoteName('#__simplefilemanager'));
        $query->where($db->quoteName('id') . ' = ' . $id);
        $db->setQuery($query);
        $row = $db->loadAssoc();
        

        // Check if file exists in database
        if (!$row)
            throw new Exception("No input file", 403);

        // Check if file exists on file system
        if (!file_exists($row['file_name']))
            throw new Exception("File not found", 404);

        // Check permissions
        if ($row['state'] != '1' or ($row['visibility'] == 2 and ! $user->id) or // Registred
            ($row['visibility'] == 3 and $user->id != $row['reserved_user']) or // User
            ($row['visibility'] == 4 and ! in_array($row['reserved_group'], JAccess::getGroupsByUser($user->id))) or // Group
            ($row['visibility'] == 5 and $user->id != $row['author']) // Author
		)
            throw new Exception("No access", 403);

        // Increment download counter
        $db1 = JFactory::getDbo();
        $query1 = $db1->getQuery(true);
        $db1->setQuery("UPDATE #__simplefilemanager SET download_counter = download_counter + 1, download_last = NOW() WHERE id = " . $id);
        $db1->execute();

        $this->file_name = $row['file_name'];

        parent::display($tpl);
    }
}
