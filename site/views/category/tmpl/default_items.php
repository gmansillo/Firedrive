<?php
/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<?php if (empty($this->items)) : ?>
	<p> <?php echo JText::_('COM_SIMPLEFILEMANAGER_NO_DOCUMENTS'); ?></p>
<?php else : ?>

	<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
	<?php if ($this->params->get('show_pagination_limit', 1)) : ?>
	<fieldset class="filters btn-toolbar">
		<?php if ($this->params->get('show_pagination_limit', 1)) : ?>
			<div class="btn-group pull-right">
				<label for="limit" class="element-invisible">
					<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
				</label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		<?php endif; ?>
	</fieldset>
	<?php endif; ?>

		<ul class="category row-striped">
			<?php foreach ($this->items as $i => $item) : ?>

				<?php if (in_array($item->access, $this->user->getAuthorisedViewLevels())) : ?>
					<?php if ($this->items[$i]->state == 0) : ?>
						<li class="row-fluid system-unpublished cat-list-row<?php echo $i % 2; ?>">
					<?php else : ?>
						<li class="row-fluid cat-list-row<?php echo $i % 2; ?>" >
					<?php endif; ?>

					<?php if($this->params->get('show_icon',1)): ?>
					
						<div class="list-title span1 col-md-1">
						<?php $full_width = 5; ?>
						<?php if (!empty($this->items[$i]->icon)) : ?>
							<h3>
								<a style="padding-bottom:5px; padding-right:5px" href="<?php echo JRoute::_(SimplefilemanagerHelperRoute::getDocumentRoute($item->slug, $item->catid)); ?>">
								<?php echo JHtml::_('image', $this->items[$i]->icon, JText::_('COM_SIMPLEFILEMANAGER_IMAGE_DETAILS'), array('class' => 'document-thumbnail img-thumbnail', 'alt' => '')); ?>
								</a>
							</h3>
						<?php endif; ?>
						</div>
					
					<?php else: ?>
					
						<?php $full_width = 7; ?>
					
					<?php endif; ?>
					
					<div class="list-title span<?php echo $full_width; ?> col-md-<?php echo $full_width; ?> ">
						<a href="<?php echo JRoute::_(SimplefilemanagerHelperRoute::getDocumentRoute($item->slug, $item->catid)); ?>">
							<h3><?php echo $item->title; ?></h3>
						</a>
						<?php if ($this->items[$i]->state == 0) : ?>
							<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
						<?php endif; ?>
						<?php echo $item->event->afterDisplayTitle; ?>

						<?php 

							$details = array();

							if ($this->params->get('show_created_by', 1) && !empty ($item->created_by))
							{
								$details[] = $item->created_by_name;
							}
							if ($this->params->get('show_created', 1) && !empty($item->created))
							{
								$details[] = $item->created;
							}
							if ($this->params->get('show_file_size', 1) && !empty($item->file_size))
							{
								$file_size = SimplefilemanagerHelper::convertToReadableSize($item->file_size);
								$details[] = $file_size;
							}
							if ($this->params->get('show_license', 1) && !empty($item->license))
							{
								if(!empty($item->license_link)){
									$details[] = '<a href="'.$item->license_link.'">'.$item->license.'</a>';
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

						<div class="hidden-phone hidden-xs"><?php echo $item->description; ?></div>
					
					</div>

					<div class="span2 col-md-2">
						<?php if ($this->params->get('show_download_in_category_view', 1)) : ?>
							<?php $downloadLink = JRoute::_('index.php?option=com_simplefilemanager&amp;view=document&amp;id=' . $item->id . '&amp;format=raw'); ?>
							<a href="<?php echo $downloadLink; ?>" class="btn btn-default"><?php echo JText::_('COM_SIMPLEFILEMANAGER_DOWNLOAD_BUTTON') ?></a>
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
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
		<?php endif; ?>
		<div>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		</div>
</form>
<?php endif; ?>
