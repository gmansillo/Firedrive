<?php
/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @copyright   Firedrive
 */
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_firedrive&task=documents.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_firedrive&view=documents'); ?>" method="post" name="adminForm"
      id="adminForm">
    <div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>

            <div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>

		<?php else : ?>

            <table class="table table-striped" id="articleList">
                <thead>
                <tr>
                    <th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                    </th>
                    <th width="1%" class="center">
						<?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                    <th width="1%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                    </th>
                    <th>
						<?php echo JHtml::_('searchtools.sort', 'COM_FIREDRIVE_HEADING_TITLE', 'a.title', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
						<?php echo JText::_('COM_FIREDRIVE_HEADING_VISIBILITY'); ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
						<?php echo JText::_('COM_FIREDRIVE_HEADING_AUTHOR'); ?>
                    </th>
                    <th width="5%" class="nowrap hidden-phone">
						<?php echo JText::_('COM_FIREDRIVE_HEADING_FILE_SIZE'); ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_FIREDRIVE_HEADING_CREATED', 'a.created', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
                    </th>
                    <th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_FIREDRIVE_HEADING_DOWNLOAD_COUNTER', 'a.download_counter', $listDirn, $listOrder); ?>
                    </th>
                    <th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                    </th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td colspan="13">
						<?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
                </tfoot>
                <tbody>
				<?php
				foreach ($this->items as $i => $item) :
					$ordering = ($listOrder == 'ordering');
					$item->cat_link = JRoute::_('index.php?option=com_categories&extension=com_firedrive&task=edit&type=other&cid[]=' . $item->catid);
					$canCreate = $user->authorise('core.create', 'com_firedrive.category.' . $item->catid);
					$canEdit = $user->authorise('core.edit', 'com_firedrive.category.' . $item->catid);
					$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
					$canChange = $user->authorise('core.edit.state', 'com_firedrive.category.' . $item->catid) && $canCheckin;
					?>
                    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
                        <td class="order nowrap center hidden-phone">
							<?php
							$iconClass = '';

							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
                            elseif (!$saveOrder)
							{
								$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::_('tooltipText', 'JORDERINGDISABLED');
							}
							?>
                            <span class="sortable-handler <?php echo $iconClass ?>">
                                    <span class="icon-menu" aria-hidden="true"></span>
                                </span>
							<?php if ($canChange && $saveOrder) : ?>
                                <input type="text" style="display:none" name="order[]" size="5"
                                       value="<?php echo $item->ordering; ?>" class="width-20 text-area-order"/>
							<?php endif; ?>
                        </td>
                        <td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="center">
                            <div class="btn-group">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'documents.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
								<?php
								// Create dropdown items and render the dropdown list.
								if ($canChange)
								{
									JHtml::_('actionsdropdown.' . ((int) $item->state === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'documents');
									JHtml::_('actionsdropdown.' . ((int) $item->state === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'documents');
									echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
								}
								?>
                            </div>
                        </td>
                        <td class="has-context">
                            <div class="pull-left break-word">
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'documents.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
                                    <a class="hasTooltip"
                                       href="<?php echo JRoute::_('index.php?option=com_firedrive&task=document.edit&id=' . (int) $item->id); ?>"
                                       title="<?php echo JText::_('JACTION_EDIT'); ?>">
										<?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
									<?php echo $this->escape($item->title); ?>
								<?php endif; ?>
                                <span class="small break-word">
                                        <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                    </span>
                                <div class="small">
									<?php echo JText::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
                                </div>
                            </div>
                        </td>
                        <td class="small hidden-phone">
							<?php
							switch ($item->visibility)
							{
								case 1:
									echo JText::_('COM_FIREDRIVE_VISIBILITY_1_PUBLIC');
									break;
								case 2:
									echo JText::_('COM_FIREDRIVE_VISIBILITY_2_REGISTRED');
									break;
								case 3:
									echo JText::_('COM_FIREDRIVE_VISIBILITY_3_USER');
									break;
								case 4:
									echo JText::_('COM_FIREDRIVE_VISIBILITY_4_GROUP');
									break;
								case 5:
									echo JText::_('COM_FIREDRIVE_VISIBILITY_5_AUTHOR');
									break;
							}
							?>
                        </td>
                        <td class="small hidden-phone">
                            <a class="hasTooltip"
                               href="<?php echo JRoute::_("index.php?option=com_users&task=user.edit&id=" . (int) $item->created_by); ?>"
                               title="<?php echo JText::_('JAUTHOR'); ?>">
								<?php echo $item->author_name; ?>
                            </a>
                        </td>
                        <td class="small hidden-phone">
							<?php echo FiredriveHelper::convertToReadableSize($item->file_size); ?>
                        </td>
                        <td class="small hidden-phone">
							<?php echo $item->created; ?>
                        </td>
                        <td class="small nowrap hidden-phone">
							<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
                        </td>
                        <td class="small hidden-phone">
                            <a class="badge badge-info <?php if ($item->download_counter > 0) echo 'hasTooltip'; ?>"
                               title="<?php if ($item->download_counter > 0) echo JText::sprintf('COM_FIREDRIVE_LAST_DOWNLOAD', $item->download_last); ?>"
                               target="_blank"
                               href="<?php echo JUri::base() . "index.php?option=com_firedrive&task=download&id=" . $item->id; ?>"><i
                                        class="icon icon-download"></i> <?php echo $item->download_counter; ?>
                            </a>
                        </td>
                        <td class="hidden-phone">
							<?php echo $item->id; ?>
                        </td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>

			<?php
			// Load the batch processing form.
			if ($user->authorise('core.create', 'com_firedrive') && $user->authorise('core.edit', 'com_firedrive') && $user->authorise('core.edit.state', 'com_firedrive'))
			{
				$modal_params = array('title' => JText::_('COM_FIREDRIVE_BATCH_OPTIONS'), 'footer' => $this->loadTemplate('batch_footer'));
				echo JHtml::_('bootstrap.renderModal', 'collapseModal', $modal_params, $this->loadTemplate('batch_body'));
			}
			?>

		<?php endif; ?>

		<?php if(!empty($this->items)) echo JText::_("COM_FIREDRIVE_UPGRADE_INSTRUCTIONS"); ?>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>

		<?php echo JHtml::_('form.token'); ?>

    </div>
</form>
