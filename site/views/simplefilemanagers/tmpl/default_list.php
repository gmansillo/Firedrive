<ul id="sfm-category-list" class="media-list">

    <?php foreach ($this->items as $i => $item) : ?>
        <li class="media" id="sfm-list-item-<?php echo $i; ?>">

            <?php if ($i > 0): ?>

                <hr/>

            <?php endif; ?>

            <?php
            $this->canEdit = $this->user->authorise('core.edit', 'com_simplefilemanager');
            if (!$this->canEdit && $this->user->authorise('core.edit.own', 'com_simplefilemanager')) {
                $this->canEdit = JFactory::getUser()->id == $item->created_by;
            }
            ?>

            <?php if (isset($item->checked_out) && $item->checked_out && ($this->canEdit || $this->canChange)) : ?>
                <span class="pull-left">
					<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'simplefilemanager.', $this->canCheckin); ?>
				</span>
            <?php elseif ($this->showIcon): ?>
                <span class="pull-left">
					<img alt="<?php echo $item->title; ?>" src="<?php echo $item->icon; ?>" class="sfm-icon"/>
				</span>
            <?php endif; ?>

            <div class="pull-right">

                <div class="btn-group">

                    <?php if (isset($this->items[0]->state) and ($this->canEdit || $this->canChange)): ?>
                        <span class="btn btn-small disabled">
                        <?php if ($item->state == 1): ?>
                            <i class="icon-publish"></i> <?php echo JText::_("JPUBLISHED"); ?>
                        <?php else: ?>
                            <i class="icon-unpublish"></i> <?php echo JText::_("JUNPUBLISHED"); ?>
                        <?php endif; ?>

                        </span>
                    <?php endif; ?>

                    <?php if ($this->canEdit || $this->canDelete): ?>
                        <?php if ($this->canEdit): ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&task=simplefilemanagerform.edit&id=' . $item->id, false, 2); ?>"
                               class="btn  btn-small" type="button">
                                <?php echo JText::_("COM_SIMPLEFILEMANAGER_EDIT_TEXT"); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($this->canDelete): ?>
                            <button data-item-id="<?php echo $item->id; ?>" class="btn btn-small delete-button"
                                    type="button">
                                <?php echo JText::_("COM_SIMPLEFILEMANAGER_DELETE_TEXT"); ?>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($item->canDownload || $this->canCheckin): ?>
                        <a class="btn btn-small" target="_blank"
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

                <div class="media-info">

                    <?php
                    $details = array();
                    if ($this->showDate) $details[] = JHTML::_('date', $item->file_created, JText::_('DATE_FORMAT_LC1'));
                    if ($this->showAuth && $item->author) $details[] = JFactory::getUser($item->author)->name;
                    if ($this->showSize) $details[] = round($item->file_size * .0009765625, 2) . "kb";
                    $details[] = strtolower($ext = pathinfo($item->file_name, PATHINFO_EXTENSION));
                    if ($this->showLicence && $item->license_link) $details[] = '<a href="' . $item->license_link . '" target="_blank">' . $item->license . '</a>';
                    else if ($this->showLicence && $item->license) $details[] = $item->license;
                    if ($this->showMD5 && $item->md5hash) $details[] = $item->md5hash;
                    if ($this->showDesc && $item->description) $details[] = strip_tags($item->description);

                    echo join(' - ', $details);
                    ?>

                </div>

            </div>

            <div class="clearfix"></div>

        </li>

    <?php endforeach; ?>

</ul>

<?php echo $this->pagination->getListFooter(); ?>