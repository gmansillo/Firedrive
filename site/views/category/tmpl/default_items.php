<?php
/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;

JHtml::_('behavior.core');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">

    <?php if ($this->params->get('category_show_category_description') == 1) : ?>
        <?php echo $this->getModel()->getCategory()->description; ?>
    <?php endif; ?>

    <fieldset class="filters">

        <?php if ($this->params->get('filter_field') == 1) : ?>
            <div class="btn-group">
                <label class="filter-search-lbl element-invisible" for="filter-search"><span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span><?php echo JText::_('COM_FIREDRIVE_FILTER_SEARCH_LABEL') . '&#160;'; ?></label>
                <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_FIREDRIVE_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_FIREDRIVE_FILTER_SEARCH_DESC'); ?>" />
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
            function tableOrdering(order, dir, task)
            {
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
                if (order != '<?php echo $listOrder; ?>')
                {
                    dirn = 'asc';
                } else {
                    dirn = direction.options[direction.selectedIndex].value;
                }
                Joomla.tableOrdering(order, dirn);
            };
        </script>

        <ul class="category row-striped" role="list">
            <?php foreach ($this->items as $i => $item) : ?>

                <?php if (in_array($item->access, $this->user->getAuthorisedViewLevels())) : ?>
                    <?php if ($this->items[$i]->state == 0) : ?>
                        <li class="row-fluid system-unpublished cat-list-row<?php echo $i % 2; ?>" role="listitem" >
                        <?php else : ?>
                        <li class="row-fluid cat-list-row<?php echo $i % 2; ?>" role="listitem" >
                        <?php endif; ?>

                        <?php if ($this->params->get('category_show_document_icon') == 1): ?>

                            <div class="list-title span1 col-md-1">
                                <?php $full_width = 5; ?>
                                <?php if (!empty($this->items[$i]->icon)) : ?>
                                    <h3>
                                        <a style="padding-bottom:5px; padding-right:5px" href="<?php echo JRoute::_(FiredriveHelperRoute::getDocumentRoute($item->slug, $item->catid)); ?>">
                                            <?php echo JHtml::_('image', $this->items[$i]->icon, JText::_('COM_FIREDRIVE_IMAGE_DETAILS'), array('class' => 'document-thumbnail img-thumbnail', 'alt' => '')); ?>
                                        </a>
                                    </h3>
                                <?php endif; ?>
                            </div>

                        <?php else: ?>

                            <?php $full_width = 7; ?>

                        <?php endif; ?>

                        <div class="list-title span<?php echo $full_width; ?> col-md-<?php echo $full_width; ?> ">

                            <a href="<?php echo JRoute::_(FiredriveHelperRoute::getDocumentRoute($item->slug, $item->catid)); ?>">
                                <h3><?php echo $item->title; ?></h3>
                            </a>

                            <?php if ($this->items[$i]->state == 0) : ?>
                                <span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
                            <?php endif; ?>

                            <?php if ($this->params->get('category_show_document_new', 1)) : ?>
                                <?php
                                $created = JFactory::getDate($this->items[$i]->created)->toUnix();
                                $limit   = JFactory::getDate('now -' . $this->params->get('new_duration_limit', 7) . ' day')->toUnix();
                                ?>

                                <?php if ($created >= $limit) : ?>
                                    <span class="label label-important"><?php echo JText::_('COM_FIREDRIVE_NEW'); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php echo $item->event->afterDisplayTitle; ?>

                            <?php
                            $details = array();

                            if ($this->params->get('category_show_document_created_by') == 1 && !empty($item->created_by)) {
                                $details[] = $item->created_by_name;
                            }
                            if ($this->params->get('category_show_document_created') == 1 && !empty($item->created)) {
                                $details[] = $item->created;
                            }
                            if ($this->params->get('category_show_document_file_size') == 1 && !empty($item->file_size)) {
                                $file_size = FiredriveHelper::convertToReadableSize($item->file_size);
                                $details[] = $file_size;
                            }
                            if ($this->params->get('category_show_document_license') == 1 && !empty($item->license)) {
                                if (!empty($item->license_link)) {
                                    $details[] = '<a href="' . $item->license_link . '">' . $item->license . '</a>';
                                } else {
                                    $details[] = $item->license;
                                }
                            }

                            echo implode($details, ' - ');
                            ?>

                            <?php echo $item->event->beforeDisplayContent; ?>

                            <div class="clear clr clearfix"></div>
                        </div>

                        <div class="span4 col-md-4">

                            <?php if ($this->params->get('category_show_document_description') == 1): ?>
                                <div class="hidden-phone hidden-xs"><?php echo $item->description; ?></div>
                            <?php endif; ?>

                        </div>

                        <div class="span2 col-md-2">
                            <?php if ($this->params->get('show_download_in_category_view', 1)) : ?>
                                <?php $downloadLink = JRoute::_('index.php?option=com_firedrive&amp;view=document&amp;id=' . $item->id . '&amp;format=raw'); ?>
                                <a href="<?php echo $downloadLink; ?>" class="btn btn-default"><?php echo JText::_('COM_FIREDRIVE_DOWNLOAD_BUTTON') ?></a>
                                <br />
                            <?php endif; ?>
                        </div>

                        <?php echo $item->event->afterDisplayContent; ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>

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
            <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
            <input type="hidden" name="view" value="category" />
        </div>

    <?php endif; ?>


 
</form>