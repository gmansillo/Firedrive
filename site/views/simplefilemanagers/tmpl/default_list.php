<style>
	#sfm-category-list {
		list-style: none;
	}

	#sfm-category-list h4 {
		margin: 3px 0;
	}

	.media > .pull-left {
		margin-right: 10px;

	}

	.media > .pull-left > img {
		min-height: 32px;
		width: auto;
	}
</style>

<ul id="sfm-category-list" class="media-list">

	<?php foreach ($this->items as $i => $item) : ?>
		<li class="media" id="sfm-list-item-<?php echo $i; ?>">

			<?php if ($i > 0): ?>

				<hr/>

			<?php endif; ?>

			<?php
				$this->canEdit = $this->user->authorise('core.edit', 'com_simplefilemanager');
				if (!$this->canEdit && $this->user->authorise('core.edit.own', 'com_simplefilemanager'))
				{
					$this->canEdit = JFactory::getUser()->id == $item->created_by;
				}
			?>

			<?php if (isset($item->checked_out) && $item->checked_out && ($this->canEdit || $this->canChange)) : ?>
				<span class="pull-left" >
					<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'simplefilemanager.', $canCheckin); ?>
				</span>
			<?php elseif ($this->showIcon): ?>
				<span class="pull-left">
					<img alt="<?php echo $item->title; ?>" src="<?php echo $item->icon; ?>" class="sfm-icon"/>
				</span>
			<?php endif; ?>

			<div class="pull-right">

				<div class="btn-group">

					<?php if (isset($this->items[0]->state)): ?>
						<?php if ($this->canEdit || $this->canChange): ?>

							<?php $class = ($this->canEdit || $this->canChange) ? 'active' : 'disabled'; ?>

							<td class="center">
								<a class="btn <?php echo $class; ?>"
								   href="<?php echo ($this->canEdit || $this->canChange) ? JRoute::_('index.php?option=com_simplefilemanager&task=simplefilemanager.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
									<?php if ($item->state == 1): ?>
										<i class="icon-publish"></i>
									<?php else: ?>
										<i class="icon-unpublish"></i>
									<?php endif; ?>
								</a>
							</td>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ($this->canEdit || $this->canDelete): ?>
						<td class="center">
							<?php if ($this->canEdit): ?>
								<a href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&task=simplefilemanagerform.edit&id=' . $item->id, false, 2); ?>"
								   class="btn" type="button"><i class="icon-edit"></i></a>
							<?php endif; ?>
							<?php if ($this->canDelete): ?>
								<button data-item-id="<?php echo $item->id; ?>" class="btn delete-button"
										type="button"><i class="icon-trash"></i></button>
							<?php endif; ?>
						</td>
					<?php endif; ?>

					<?php if ($item->canDownload): ?>
						<a class="btn"
						   href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&view=download&id=' . (int)$item->id); ?>">
							<?php echo JText::_("COM_SIMPLEFILEMANAGER_DOWNLOAD_TEXT"); ?>
						</a>
					<?php endif; ?>

				</div>

			</div>

			<div class="media-body">

				<h4 class="media-heading">

					<?php if ($this->linkOnEntryTitle) : ?>

						<a href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanager&id=' . (int)$item->id); ?>">
							<?php echo $this->escape($item->title); ?>
						</a>

					<?php else: ?>

						<?php echo $this->escape($item->title); ?>

					<?php endif; ?>

					<?php if (strtotime($item->file_created) > strtotime('-' . $this->newfiledays . ' day') AND $this->showNew): ?>
						&nbsp;<span
							class="label label-important"><?php echo JText::_('COM_SIMPLEFILEMANAGER_NEW'); ?></span>
					<?php endif; ?>

					<?php if ($item->featured): ?>
						&nbsp;<span
							class="label label-warning"><?php echo JText::_('COM_SIMPLEFILEMANAGER_HOT'); ?></span>
					<?php endif; ?>

				</h4>

				<div>

					<?php
						$details = array();
						if ($this->showDate) $details[] = JHTML::_('date', $item->file_created, JText::_('DATE_FORMAT_LC1'));
						if ($this->showAuth && $item->author) $details[] = JFactory::getUser($item->author)->name;
						if ($this->showSize) $details[] = round($item->file_size * .0009765625, 2) . "kb";
						if ($this->showLicence && $item->license_link) $details[] = '<a href="' . $item->license_link . '" target="_blank">' . $item->license . '</a>';
						else if ($this->showLicence && $item->license) $details[] = $item->license;
						if ($this->showMD5) $details[] = $item->md5hash;
						if ($this->showDesc) $details[] = $item->description;

						echo join(' - ', $details);
					?>

				</div>

			</div>

			<div class="clearfix"></div>

		</li>
	<?php endforeach; ?>

</ul>

<?php echo $this->pagination->getListFooter(); ?>



<?php echo JHTML::_('grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_TITLE', 'a.title', $this->sortDirection, $this->sortColumn); ?>

<?php if ($this->showDesc): ?>
		<?php echo JHTML::_('grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_DESCRIPTION', 'a.description', $this->sortDirection, $this->sortColumn); ?>
<?php endif; ?>
<?php if ($this->showDate): ?>
		<?php echo JHTML::_('grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_CREATED', 'a.file_created', $this->sortDirection, $this->sortColumn); ?>
<?php endif; ?>

<?php if ($this->showSize): ?>
		<?php echo JHTML::_('grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_DIMENSION', 'a.file_size', $this->sortDirection, $this->sortColumn); ?>
<?php endif; ?>

<?php if ($this->showLicence): ?>
		<?php echo JHTML::_('grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_LICENSE', 'a.license', $this->sortDirection, $this->sortColumn); ?>
<?php endif; ?>

<?php if ($this->showMD5): ?>
		<?php echo JHTML::_('grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_MD5', 'a.md5hash', $this->sortDirection, $this->sortColumn); ?>
<?php endif; ?>

<?php if ($this->showAuth): ?>
		<?php echo JHTML::_('grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_AUTHOR', 'a.author', $this->sortDirection, $this->sortColumn); ?>

<?php endif; ?>
