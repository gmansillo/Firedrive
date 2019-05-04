<?php
/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @copyright   Firedrive
 */
defined('_JEXEC') or die;

JHtml::_('behavior.core');
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::base() . 'media/com_firedrive/css/firedrive.css');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="get" name="adminForm"
      id="adminForm">

	<?php if ($this->params->get('category_show_category_description', 1) == 1) : ?>
		<?php echo $this->getModel()->getCategory()->description; ?>
	<?php endif; ?>

    <fieldset id="firedrive-category-filters" class="filters">

		<?php if ($this->params->get('filter_field') == 1) : ?>
            <div class="btn-group">
                <label class="filter-search-lbl element-invisible" for="filter-search"><span
                            class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span><?php echo JText::_('COM_FIREDRIVE_FILTER_SEARCH_LABEL') . '&#160;'; ?>
                </label>
                <input type="text" name="filter-search" id="filter-search"
                       value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox"
                       onchange="document.adminForm.submit();"
                       title="<?php echo JText::_('COM_FIREDRIVE_FILTER_SEARCH_DESC'); ?>"
                       placeholder="<?php echo JText::_('COM_FIREDRIVE_FILTER_SEARCH_DESC'); ?>"/>
            </div>
		<?php endif; ?>

		<?php if ($this->params->get('show_pagination_limit') == 1) : ?>
            <div class="pull-right">
                &nbsp;
                <label for="limit" class="element-invisible">
					<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
                </label>
				<?php echo $this->pagination->getLimitBox(); ?>
            </div>
		<?php endif; ?>

		<?php if ($this->params->get('show_ordering_select') == 1) : ?>
            <div class="pull-right">
                &nbsp;
                <select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
                    <option value=""><?php echo JText::_('JFIELD_ORDERING_LABEL'); ?></option>
                    <option value="asc" <?php echo $listDirn == 'asc' ? 'selected="selected"' : ""; ?>>
						<?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?>
                    </option>
                    <option value="desc" <?php echo $listDirn == 'desc' ? 'selected="selected"' : ""; ?>>
						<?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?>
                    </option>
                </select>
            </div>

            <div class="pull-right">
                &nbsp;
                <select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
                    <option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
					<?php echo JHtml::_('select.options', $this->sortFields, 'value', 'text', $listOrder); ?>
                </select>
            </div>
		<?php endif; ?>

    </fieldset>

	<?php if (empty($this->items)) : ?>
        <p> <?php echo JText::_('COM_FIREDRIVE_NO_DOCUMENTS'); ?></p>
	<?php else : ?>

        <script language="javascript" type="text/javascript">
            function tableOrdering(order, dir, task) {
                var form = document.adminForm;

                form.filter_order.value = order;
                form.filter_order_Dir.value = dir;
                document.adminForm.submit(task);
            }
        </script>
        <script type="text/javascript">
            Joomla.orderTable = function () {
                table = document.getElementById("sortTable");
                direction = document.getElementById("directionTable");
                order = table.options[table.selectedIndex].value;
                if (order != '<?php echo $listOrder; ?>') {
                    dirn = 'asc';
                } else {
                    dirn = direction.options[direction.selectedIndex].value;
                }
                Joomla.tableOrdering(order, dirn);
            };
        </script>

        <style>
            @media only screen and (max-width: 760px),
            (min-device-width: 768px) and (max-device-width: 1024px) {
                #firedrive-category-table .col-title:before {
                    content: "<?php echo JText::_('COM_FIREDRIVE_CATEGORIES_DOCUMENT_TITLE_LABEL'); ?>";
                }

                #firedrive-category-table .col-size:before {
                    content: "<?php echo JText::_('COM_FIREDRIVE_CATEGORIES_DOCUMENT_SIZE_LABEL'); ?>";
                }

                #firedrive-category-table .col-created:before {
                    content: "<?php echo JText::_('COM_FIREDRIVE_CATEGORIES_DOCUMENT_LAST_EDIT_LABEL'); ?>";
                }
            }
        </style>

        <table class="table table-hover" id="firedrive-category-table" role="list">
            <thead>
            <tr>
				<?php if ($this->params->get('category_show_document_icon') == 1): ?>
                    <th></th>
				<?php endif; ?>
                <th>
					<?php echo JText::_('COM_FIREDRIVE_CATEGORIES_DOCUMENT_TITLE_LABEL'); ?>
                </th>
				<?php
				$show_last_edit_by = $this->params->get('category_show_document_modified_by', 0) == 1;
				$show_last_edit    = $this->params->get('category_show_document_modified', 1) == 1
				?>
				<?php if ($this->params->get('category_show_document_file_size') == 1): ?>
                    <th><?php echo JText::_('COM_FIREDRIVE_CATEGORIES_DOCUMENT_SIZE_LABEL'); ?></th>
				<?php endif; ?>
				<?php if ($show_last_edit_by || $show_last_edit): ?>
                    <th><?php echo JText::_('COM_FIREDRIVE_CATEGORIES_DOCUMENT_LAST_EDIT_LABEL'); ?></th>
				<?php endif; ?>
                <th></th>

            </tr>
            </thead>

            <tbody>
			<?php foreach ($this->items as $i => $item) : ?>
                <tr class="<?php if ($this->items[$i]->state == 0) echo 'system-unpublished'; ?> cat-list-row<?php echo $i % 2; ?>"
                    role="listitem">
					<?php if ($this->params->get('category_show_document_icon') == 1): ?>
                        <td class="col-icon">
							<?php echo JHtml::_('image', $this->items[$i]->icon, "", array('class' => 'document-thumbnail img-thumbnail')); ?>
                        </td>
					<?php endif; ?>
                    <td class="col-title">
                        <strong>
							<?php if ($this->params->get('show_link_on_title_in_category_view', 1) == 1): ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_firedrive&amp;view=document&amp;id=' . $item->id . '&amp;format=raw'); ?>"><?php echo $item->title; ?></a>
							<?php else: ?>
								<?php echo $item->title; ?>
							<?php endif; ?>

							<?php echo $item->event->afterDisplayTitle; ?>
                            &nbsp;
							<?php if ($this->items[$i]->state == 0) : ?>
                                <span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
							<?php endif; ?>
							<?php if ($this->params->get('category_show_document_new', 1)) :
								$modified = JFactory::getDate($this->items[$i]->modified)->toUnix();
								$limit   = JFactory::getDate('now -' . $this->params->get('new_duration_limit', 7) . ' day')->toUnix();
								if ($modified >= $limit) echo '<span class="label label-important">', JText::_('COM_FIREDRIVE_NEW'), '</span>';
							endif; ?>
                        </strong>
						<?php if ($this->params->get('category_show_document_license') == 1 && $item->license): ?>
							<?php echo JText::_('COM_FIREDRIVE_CATEGORIES_DOCUMENT_LICENSE_LABEL'); ?>:
							<?php
							if (empty($item->license_link)):
								echo '<a href="' . $item->license_link . '">' . $item->license . '</a>';
							else:
								echo $item->license;
							endif;
							?>
						<?php endif; ?>
                    </td>

					<?php echo $item->event->beforeDisplayContent; ?>
					<?php if ($this->params->get('category_show_document_file_size') == 1): ?>
                        <td class="col-size">
							<?php echo FiredriveHelper::convertToReadableSize($item->file_size); ?>
                        </td>
					<?php endif; ?>
					<?php if ($show_last_edit || $show_last_edit_by): ?>
                        <td class="col-last_edit">
							<?php if ($show_last_edit_by) echo $item->modified_by_name; ?>
							<?php if ($show_last_edit && $show_last_edit_by) echo '<br>'; ?>
							<?php if ($show_last_edit) echo JHtml::_('date', $item->modified); ?>
                        </td>
					<?php endif; ?>
                    <td class="col-actions">
                        <a title="<?php echo JText::_("COM_FIREDRIVE_DETAILS_LABEL"); ?>" href="<?php echo JRoute::_(FiredriveHelperRoute::getDocumentRoute($item->slug, $item->catid)); ?>"
                           class="btn btn-default btn-sm btn-small">...</a>
                    </td>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>

	<?php echo $item->event->afterDisplayContent; ?>

	<?php if ($this->params->get('show_pagination', 2)) : ?>
        <div class="pagination">
			<?php if ($this->params->def('show_pagination_results', 1)) : ?>
                <p class="counter">
					<?php echo $this->pagination->getPagesCounter(); ?>
                </p>
			<?php endif; ?>
			<?php echo $this->pagination->getListFooter(); ?>
        </div>
	<?php endif; ?>

        <div>
            <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
            <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
            <input type="hidden" name="view" value="category"/>
        </div>

	<?php endif; ?>


</form>