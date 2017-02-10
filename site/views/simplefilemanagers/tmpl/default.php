<?php

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

?>

<h2>
    <?php echo $this->params->get('page_heading'); ?>
</h2>

<div class="cat-desc">
    <?php echo $this->catDesc; ?>
</div>

<form
    class="simplefilamanager"
    action="<?php echo JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanagers'); ?>"
    method="post"
    name="adminForm"
    id="adminForm"
    >

    <div id="filter-bar" class="js-stools-container-bar clearfix">

        <div class="pull-left">
            <?php if ($this->canCreate): ?>
                <a href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&task=simplefilemanagerform.edit&id=0', false, 2); ?>"
                   class="btn btn-success btn-small">
                    <i class="icon-plus"></i> <?php echo JText::_('COM_SIMPLEFILEMANAGER_ADD_ITEM'); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="pull-right">

            <?php if ($this->enableOrderingSelect): ?>
                &nbsp;
                <select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
                    <option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
                    <option value="asc" <?php echo $this->sortDirection == 'asc' ? 'selected="selected"' : ""; ?>>
                        <?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?>
                    </option>
                    <option value="desc" <?php echo $this->sortDirection == 'desc' ? 'selected="selected"' : ""; ?>>
                        <?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?>
                    </option>
                </select>
            <?php endif; ?>
        </div>

        <div class="pull-right">
            <?php if ($this->enableOrderingSelect): ?>
                <select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
                    <option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
                    <?php echo JHtml::_('select.options', $this->sortFields, 'value', 'text', $this->sortColumn); ?>
                </select>
            <?php endif; ?>
        </div>
    </div>

    <?php
    if (count($this->items) > 0) {
        echo $this->loadTemplate($this->subview);
    } else if (!count($this->items) and $this->params->get('nomediaMessage')):
        ?>

        <div class="nomedia">
            <p><i class="icon-file-remove"></i> <?php echo JText::_('COM_SIMPLEFILEMANAGER_ERR_NOMEDIA'); ?></p>
        </div>

    <?php endif; ?>

    <?php if (count($this->children) > 0): ?>

        <div class="children-cats">
            <ul>
                <?php foreach ($this->children as $child): ?>
                    <li>
                        <a href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&catid=' . $child->id, false); ?>">
                            <?php echo $child->title; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>

    <?php echo JHtml::_('form.token'); ?>
</form>

<script language="javascript" type="text/javascript">

    Joomla.orderTable = function () {
        table = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order = table.options[table.selectedIndex].value;
        dirn = direction.options[direction.selectedIndex].value;
        if (dirn == "") dirn = "asc";
        Joomla.tableOrdering(order, dirn, '');
    }

    function tableOrdering(order, dir, task) {
        var form = document.adminForm;

        form.filter_order.value = order;
        form.filter_order_Dir.value = dir;
        document.adminForm.submit(task);
    }

    jQuery(document).ready(function () {
        jQuery('.delete-button').click(deleteItem);
    });

    function deleteItem() {
        var item_id = jQuery(this).attr('data-item-id');
        if (confirm("<?php echo JText::_('COM_SIMPLEFILEMANAGER_DELETE_MESSAGE'); ?>")) {
            window.location.href = '<?php echo JRoute::_('index.php?option=com_simplefilemanager&task=simplefilemanagerform.remove&id=', false, 2) ?>' + item_id;
        }
    }
</script>
