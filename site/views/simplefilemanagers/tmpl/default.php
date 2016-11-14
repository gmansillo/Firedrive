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

<form class="simplefilamanager"
	  action="<?php echo JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanagers'); ?>" method="post"
	  name="adminForm" id="adminForm">

	<?php
		if (count($this->items) > 0)
		{
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

	<?php if ($this->canCreate): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&task=simplefilemanagerform.edit&id=0', false, 2); ?>"
		   class="btn btn-success btn-small">
			<i class="icon-plus"></i> <?php echo JText::_('COM_SIMPLEFILEMANAGER_ADD_ITEM'); ?>
		</a>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>

	<?php echo JHtml::_('form.token'); ?>
</form>

<script language="javascript" type="text/javascript">
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
