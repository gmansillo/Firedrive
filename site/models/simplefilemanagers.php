<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class SimplefilemanagerModelSimplefilemanagers extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				 	'id', 'a.id',
				'title', 'a.title',
				'state', 'a.state',
				'description', 'a.description',
				'ordering', 'a.ordering',
				'reserved_user', 'a.reserved_user',
				'file_created', 'a.file_created',
				'file_size', 'a.file_size',
				'author', 'a.author',
				'md5hash', 'a.md5hash',
				'license', 'a.license',
			);
		}
		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since    1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app 	= JFactory::getApplication();
		$params = JComponentHelper::getParams('com_simplefilemanager');
		$catid 	= JRequest::getInt('catid',0);

		// Load the parameters.
		$defOrderingField 		= $app->input->getString('orderBy','file_created');
		$defOrderingDirection 	= $app->input->getString('showIcon', 'ASC');

		$this->setState('params', $params);
		$this->setState('catid', $catid);
		
		// Optional filter text
		$this->setState('list.filter', $app->input->getString('filter-search'));

		parent::populateState($defOrderingField, $defOrderingDirection);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$app            = JFactory::getApplication('site');
		$user 			= JFactory::getUser();
		$groups         = implode(',', $user->getAuthorisedViewLevels());
		$params			= JComponentHelper::getParams('com_simplefilemanager');
		
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		
		// Select the required fields from the table.
		$query
			->select(
				$this->getState(
					'list.select', 'DISTINCT a.*'
				)
			);
		
		$query->from('`#__simplefilemanager` AS a');
		
	    // Join over the users for the checked out user.
	    $query->select('uc.name AS editor');
	    $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
    
		// Join over the created by field 'created_by'
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		if (!JFactory::getUser()->authorise('core.manage', 'com_simplefilemanager'))
		{

			if( $params->get('forceListVisibility',0) == 1)
			{
				// Can't see only documents reserved to a single user (included the author)
				$query->where("((a.visibility = 1) OR ((a.visibility = 3) AND (a.reserved_user=".$user->id.")) OR (a.visibility = 2) OR ((a.visibility = 5) AND (a.author=".$user->id.")) OR (a.visibility = 4))");
			}
			else
			{
				$query->where("((a.visibility = 1) OR ((a.visibility = 3) AND (a.reserved_user=".$user->id.")) OR ((a.visibility = 2) AND (".$user->id.">0)) OR ((a.visibility = 5) AND (a.author=".$user->id.")) OR ((a.visibility = 4) AND ( a.reserved_group IN (".implode(", ", JAccess::getGroupsByUser($user->id))."))))");
			}
		}

		if (!JFactory::getUser()->authorise('core.edit.state', 'com_simplefilemanager'))
		{
			$query->where('a.state = 1');
		}
		
		if ($categoryId=$this->getState('catid') AND $categoryId!=0)
		{
			$query->where('a.catid = '.(int) $categoryId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				
			}
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.file_created')).' '. $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	public function getItems()
	{
		// Invoke the parent getItems method to get the main list
		$items = parent::getItems();

		  return $items;
	}

    public function getPagination()
    {
        if (empty($this->pagination))
        {
            // Import the pagination library
            JLoader::import('joomla.html.pagination');

            // Prepare pagination values
            $total = $this->getTotal();
            $limitstart = $this->getState('limitstart');
            $limit = $this->getState('limit');

            // Create the pagination object
            $this->pagination = new JPagination($total, $limitstart, $limit);
        }

        return $this->pagination;
    }

	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 */
	protected function loadFormData()
	{
		$app              = JFactory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$error_dateformat = false;
		foreach ($filters as $key => $value)
		{
			if (strpos($key, '_dateformat') && !empty($value) && !$this->isValidDate($value))
			{
				$filters[$key]    = '';
				$error_dateformat = true;
			}
		}
		if ($error_dateformat)
		{
			$app->enqueueMessage(JText::_("COM_SIMPLEFILEMANAGER_SEARCH_FILTER_DATE_FORMAT"), "warning");
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}

	/**
	 * Checks if a given date is valid and in an specified format (YYYY-MM-DD)
	 * @param string Contains the date to be checked
	 */
	private function isValidDate($date)
	{
		return preg_match("/^(19|20)\d\d[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])$/", $date) && date_create($date);
	}

}
