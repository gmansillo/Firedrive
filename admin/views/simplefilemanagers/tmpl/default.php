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
JHTML::_('behavior.modal');

//Get category options
JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canOrder  = $user->authorise('core.edit.state', 'com_simplefilemanager.category');
$saveOrder = $listOrder == 'a.ordering';
$archived  = $this->state->get('filter.state') == 2 ? true : false;
$trashed   = $this->state->get('filter.state') == -2 ? true : false;

if ($saveOrder)
{
    $saveOrderingUrl = 'index.php?option=com_content&task=articles.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}


if ($saveOrder)
{
    $saveOrderingUrl = 'index.php?option=com_simplefilemanager&task=simplefilemanagers.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'simplefilemanagerList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
    Joomla.orderTable = function () {
        table = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order = table.options[table.selectedIndex].value;
        if (order != '<?php echo $listOrder; ?>') {
            dirn = 'asc';
        }
        else {
            dirn = direction.options[direction.selectedIndex].value;
        }
        Joomla.tableOrdering(order, dirn, '');
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanagers'); ?>" method="post"
      name="adminForm" id="adminForm">
    <?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <?php else : ?>
        <div id="j-main-container">
            <?php endif; ?>
            <?php echo '<div class="alert alert-info">' . JText::_('COM_SIMPLEFILEMANAGER_UPGRADE_INSTRUCTIONS') . '</div>'; ?>

            <div id="filter-bar" class="js-stools-container-bar clearfix">
                <div class="filter-search btn-group pull-left">
                    <label for="filter_search"
                           class="element-invisible"><?php echo JText::_('COM_SIMPLEFILEMANAGER_SEARCH_IN_TITLE'); ?></label>
                    <input type="text" name="filter_search" id="filter_search"
                           placeholder="<?php echo JText::_('COM_SIMPLEFILEMANAGER_SEARCH_IN_TITLE'); ?>"
                           value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                           title="<?php echo JText::_('COM_SIMPLEFILEMANAGER_SEARCH_IN_TITLE'); ?>"/>
                </div>
                <div class="btn-group pull-left">
                    <button class="btn hasTooltip" type="submit"
                            title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i>
                    </button>
                    <button class="btn hasTooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
                            onclick="document.id('filter_search').value='';this.form.submit();"><i
                            class="icon-remove"></i></button>
                </div>

                <div class="btn-group pull-right hidden-phone">
                    <label for="limit"
                           class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>
                <div class="btn-group pull-right hidden-phone">
                    <label for="directionTable"
                           class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
                    <select name="directionTable" id="directionTable" class="input-medium"
                            onchange="Joomla.orderTable()">
                        <option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
                        <option value="asc" <?php if ($listDirn == 'asc')
                        {
                            echo 'selected="selected"';
                        } ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
                        <option value="desc" <?php if ($listDirn == 'desc')
                        {
                            echo 'selected="selected"';
                        } ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?></option>
                    </select>
                </div>
                <div class="btn-group pull-right hidden-phone">
                    <label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
                    <select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
                        <option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
                        <?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
                    </select>
                </div>

            </div>

            <?php if (empty($this->items)) : ?>
                <div class="alert alert-no-items">
                    <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                </div>
            <?php else : ?>
                <table class="table table-striped" id="simplefilemanagerList">
                    <thead>
                    <tr>
                        <!--<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
						</th>-->

                        <th width="1%" class="center hidden-phone">
                            <input type="checkbox" name="checkall-toggle" value=""
                                   title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
                                   onclick="Joomla.checkAll(this)"/>
                        </th>

                        <th width="5%" style="min-width:55px" class="nowrap center">
                            <?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                        </th>

                        <th class="has-context">
                            <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>

                        <th width="10%" class="hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_SIMPLEFILEMANAGER_FIELD_VISIBILITY_LABEL', 'a.visibility', $listDirn, $listOrder); ?>
                        </th>

                        <th width="10%" class="hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'JAUTHOR', 'a.author', $listDirn, $listOrder); ?>
                        </th>

                        <th width="10%" class="hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_CREATED', 'a.file_created', $listDirn, $listOrder); ?>
                        </th>

                        <th width="1%" class="hidden-phone">
                            <?php echo JHtml::_('grid.sort', '<i class="icon-download"></i>', 'a.download_counter', $listDirn, $listOrder, null, 'asc', 'COM_SIMPLEFILEMANAGER_HEADING_HITS'); ?>
                        </th>

                        <th width="1%" class="center">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                    </thead>

                    <tfoot>
                    <tr>
                        <td colspan="11">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                    </tfoot>

                    <tbody>
                    <?php foreach ($this->items as $i => $item) :
                        $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
                        $canChange  = $user->authorise('core.edit.state', 'com_simplefilemanager') && $canCheckin;
                        $canEdit    = $user->authorise('core.edit', 'com_simplefilemanager.category.' . $item->catid);
                        ?>

                        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="1">
                            <!--<td class="order nowrap center hidden-phone">
						<?php if ($canChange) :
                                $disableClassName = '';
                                $disabledLabel    = '';
                                if (!$saveOrder) :
                                    $disabledLabel    = JText::_('JORDERINGDISABLED');
                                    $disableClassName = 'inactive tip-top';
                                endif; ?>
							<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>" title="<?php echo $disabledLabel ?>">
								<i class="icon-menu"></i>
							</span>
							<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
						<?php else : ?>
							<span class="sortable-handler inactive" >
								<i class="icon-menu"></i>
							</span>
						<?php endif; ?>
						</td>-->

                            <td class="center hidden-phone">
                                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>

                            <td class="nowrap center">
                                <div class="btn-group">
                                    <?php echo JHtml::_('jgrid.published', $item->state, $i, 'simplefilemanagers.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
                                    <?php echo JHtml::_('contentadministrator.featured', $item->featured, $i, $canChange); ?>
                                    <?php
                                    // Create dropdown items
                                    $action = $archived ? 'unarchive' : 'archive';
                                    JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'simplefilemanagers');

                                    $action = $trashed ? 'untrash' : 'trash';
                                    JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'simplefilemanagers');

                                    // Render dropdown list
                                    echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
                                    ?>
                                </div>
                            </td>

                            <td class="has-context">
                                <?php if ($item->checked_out) : ?>
                                    <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'simplefilemanagers.', $canCheckin); ?>
                                <?php endif; ?>
                                <?php if ($canEdit) : ?>
                                    <a class="hasTooltip"
                                       href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&task=simplefilemanager.edit&id=' . $item->id); ?>"
                                       title="<?php echo JText::_('JACTION_EDIT'); ?>"><?php echo $this->escape($item->title); ?></a>
                                <?php else : ?>
                                    <span
                                        title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
                                <?php endif; ?>
                                <br>
							<span class="small hidden-phone">
								<?php echo JText::_('JCATEGORY'); ?>: <?php echo $item->category_title; ?>
							</span>
                            </td>

                            <td class="small hidden-phone">
                                <?php
                                switch ($item->visibility)
                                {
                                    case 1:
                                        echo JText::_("COM_SIMPLEFILEMANAGER_VISIBILITY_PUBLIC");
                                        break;
                                    case 2:
                                        echo JText::_("COM_SIMPLEFILEMANAGER_VISIBILITY_REGISTRED");
                                        break;
                                    case 3:
                                        echo '<a target="_blank" href="index.php?option=com_users&task=user.edit&id=' . $item->reserved_user . '">' . JFactory::getUser($item->reserved_user)->name . '</a>';
                                        break;
                                    case 4:
                                        $db    = JFactory::getDBO();
                                        $query = $db->getQuery(true);
                                        $query->select('id, title')->from('#__usergroups')->where('id = ' . $item->reserved_group);
                                        $db->setQuery($query);
                                        $row = $db->loadRow();
                                        echo '<a target="_blank" href="index.php?option=com_users&view=group&layout=edit&id=' . $row[0] . '">' . $row[1] . '</a>';
                                        break;
                                    case 5:
                                        echo '<a target="_blank" href="index.php?option=com_users&task=user.edit&id=' . $item->author . '">' . JFactory::getUser($item->author)->name . '</a>';
                                        break;
                                }

                                ?>
                            </td>

                            <td class="small hidden-phone">
                                <?php echo '<a target="_blank" href="index.php?option=com_users&task=user.edit&id=' . $item->author . '">' . JFactory::getUser($item->author)->name . '</a>'; ?>
                            </td>

                            <td class="hidden-phone small">
                                <?php echo date_format(date_create($item->file_created), JText::_('DATE_FORMAT_LC4')); ?>
                            </td>

                            <td class="hidden-phone">
							<span>
								<?php echo $item->download_counter; ?>
							</span>
                            </td>

                            <td class="center">
                                <?php echo (int)$item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>

            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="boxchecked" value="0"/>
            <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
            <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
            <?php echo JHtml::_('form.token'); ?>
        </div>
</form>


<div id="importMassive" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">

    <div class="modal-header">
        <h2 id="myModalLabel" class="text-center">
            <?php if (count($this->massiveFileImportQueue) == 0): ?>
                Carica i tuoi file
            <?php else: ?>
                Pronti per l'importazione!
            <?php endif; ?>
        </h2>
    </div>

    <div class="modal-body">
        <?php if (count($this->massiveFileImportQueue) == 0): ?>
            <p class="text-center"><?php echo JText::_('COM_SIMPLEFILEMANAGER_MASSIMPORT_DESCRIPTION'); ?></p>
        <?php else: ?>
            <form class="text-center" method="post"
                  action="<?php echo JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanagers&task=massiveImport'); ?>">
                <p class="text-center"><?php echo JText::sprintf("COM_SIMPLEFILEMANAGER_MASSIMPORT_SUMMARY", count($this->massiveFileImportQueue)); ?></p>

                <p><input type="submit" value="Avvia" class="btn btn-large btn-success"/></p>

                <p class="small"><?php echo JText::_('COM_SIMPLEFILEMANAGER_MASSIMPORT_FILELIST'); ?><?php echo implode($this->massiveFileImportQueue, ", "); ?></p>

                <input type="hidden" name="fileCount" value="<?php echo count($this->massiveFileImportQueue); ?>">

                <?php foreach ($this->massiveFileImportQueue as $key => $i): ?>
                    <input type="hidden" name="item[<?php echo $key; ?>]" value="<?php echo $i; ?>">
                <?php endforeach; ?>

            </form>
        <?php endif; ?>
    </div>

    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_("JCANCEL"); ?></button>
    </div>

</div>

<script type="text/javascript">

    Joomla.submitbutton = function (task) {
        return Joomla.submitform(task, document.getElementById('adminForm'));
    }

</script>