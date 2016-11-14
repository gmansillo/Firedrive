<table class="table table-striped" id="simplefilemanagerList">
    <thead>
    <tr>
        <?php if (isset($this->items[0]->state)): ?>
            <?php if ($this->canEdit || $this->canDelete): ?>
                <th width="1%" class="nowrap center">
                    <?php echo JText::_('JSTATUS'); ?>
                </th>
            <?php endif; ?>
        <?php endif; ?>

        <th class='left'>
            <?php echo JHTML::_('grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_TITLE', 'a.title', $this->sortDirection, $this->sortColumn); ?></th>
        </th>

        <?php if($this->showDesc): ?>
                        <th class='left'>
                            <?php echo JHTML::_( 'grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_DESCRIPTION', 'a.description', $this->sortDirection, $this->sortColumn); ?></th>
                        </th>
                    <?php endif; ?>
        <?php if($this->showDate): ?>
                        <th class='left'>
                            <?php echo JHTML::_( 'grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_CREATED', 'a.file_created', $this->sortDirection, $this->sortColumn); ?></th>
                        </th>
                    <?php endif; ?>

        <?php if($this->showSize): ?>
                        <th class='left'>
                            <?php echo JHTML::_( 'grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_DIMENSION', 'a.file_size', $this->sortDirection, $this->sortColumn); ?></th>
                        </th>
                    <?php endif; ?>

        <?php if($this->showLicence): ?>
                        <th class='left'>
                            <?php echo JHTML::_( 'grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_LICENSE', 'a.license', $this->sortDirection, $this->sortColumn); ?></th>
                        </th>
                    <?php endif; ?>

        <?php if($this->showMD5): ?>
                        <th class='left'>
                            <?php echo JHTML::_( 'grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_MD5', 'a.md5hash', $this->sortDirection, $this->sortColumn); ?></th>
                        </th>
                    <?php endif; ?>

        <?php if($this->showAuth): ?>
                        <th class='left'>
                            <?php echo JHTML::_( 'grid.sort', 'COM_SIMPLEFILEMANAGER_HEADING_AUTHOR', 'a.author', $this->sortDirection, $this->sortColumn); ?></th>
                        </th>
                    <?php endif; ?>

        <th>
            <?php echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_FILE'); ?>
        </th>

        <?php if ($this->canEdit || $this->canDelete): ?>
            <th class="center">
                <?php echo JText::_('COM_SIMPLEFILEMANAGER_SIMPLEFILEMANAGERS_ACTIONS'); ?>
            </th>
        <?php endif; ?>

    </tr>
    </thead>
    <tfoot>
    <tr>
        <td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
            <?php echo $this->pagination->getListFooter(); ?>
        </td>
    </tr>
    </tfoot>
    <tbody>
    <?php foreach ($this->items as $i => $item) : ?>
        <?php $this->canEdit = $this->user->authorise('core.edit', 'com_simplefilemanager'); ?>

        <?php if (!$this->canEdit && $this->user->authorise('core.edit.own', 'com_simplefilemanager')): ?>
            <?php $this->canEdit = JFactory::getUser()->id == $item->created_by; ?>
        <?php endif; ?>

        <tr class="row<?php echo $i % 2; ?>">

            <?php if (isset($this->items[0]->state)): ?>
                <?php if ($this->canEdit || $this->canChange): ?>

                    <?php $class = ($this->canEdit || $this->canChange) ? 'active' : 'disabled'; ?>

                    <td class="center">
                        <a class="btn btn-micro <?php echo $class; ?>"
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

            <td>
                <?php if (isset($item->checked_out) && $item->checked_out && ($this->canEdit || $this->canChange)) : ?>
                    <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'simplefilemanager.', $canCheckin); ?>
                <?php endif; ?>

                <?php if ($this->params->get('linkOnTitle', 1)): ?>
                <a href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&view=simplefilemanager&id=' . (int)$item->id); ?>">
                    <?php endif; ?>

                    <?php echo $this->escape($item->title); ?>

                    <?php if ($this->params->get('linkOnTitle', 1)): ?>
                </a>
            <?php endif; ?>

                <?php if (strtotime($item->file_created) > strtotime('-' . $this->newfiledays . ' day') AND $this->showNew): ?>
                    &nbsp;<span
                        class="label label-important"><?php echo JText::_('COM_SIMPLEFILEMANAGER_NEW'); ?></span>
                <?php endif; ?>

                <?php if ($item->featured): ?>
                    &nbsp;<span
                        class="label label-warning"><?php echo JText::_('COM_SIMPLEFILEMANAGER_HOT'); ?></span>
                <?php endif; ?>
            </td>

            <?php if ($this->showDesc): ?>
                <td>
                    <?php echo $item->description; ?>
                </td>
            <?php endif; ?>

            <?php if ($this->showDate): ?>
                <td>
                    <?php echo JHTML::_('date', $item->file_created, JText::_('DATE_FORMAT_LC1')); ?>
                </td>
            <?php endif; ?>

            <?php if ($this->showSize): ?>
                <td>
                    <?php echo round($item->file_size * .0009765625, 2); ?>Kb
                </td>
            <?php endif; ?>

            <?php if ($this->showLicence): ?>
                <td>
                    <?php if ($item->license_link)
                    {
                        echo '<a href="' . $item->license_link . '" target="_blank">' . $item->license . '</a>';
                    }
                    else
                    {
                        echo $item->license;
                    } ?>
                </td>
            <?php endif; ?>

            <?php if ($this->showMD5): ?>
                <td>
                    <?php echo $item->md5hash; ?>
                </td>
            <?php endif; ?>

            <?php if ($this->showAuth): ?>
                <td>
                    <?php if ($item->author): ?>
                        <?php echo JFactory::getUser($item->author)->name; ?>
                    <?php endif; ?>
                </td>
            <?php endif; ?>

            <td>

                <?php if ($item->canDownload): ?>
                    <?php if ($this->showIcon): ?>
                        <a href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&view=download&id=' . (int)$item->id); ?>">
                            <img alt="<?php echo $item->title; ?>" src="<?php echo $item->icon; ?>"
                                 class="sfm-icon"/>
                        </a>
                    <?php else: ?>
                        <a class="btn"
                           href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&view=download&id=' . (int)$item->id); ?>">
                            <?php echo JText::_("COM_SIMPLEFILEMANAGER_DOWNLOAD_TEXT"); ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

            </td>


            <?php if ($this->canEdit || $this->canDelete): ?>
                <td class="center">
                    <?php if ($this->canEdit): ?>
                        <a href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&task=simplefilemanagerform.edit&id=' . $item->id, false, 2); ?>"
                           class="btn btn-mini" type="button"><i class="icon-edit"></i></a>
                    <?php endif; ?>
                    <?php if ($this->canDelete): ?>
                        <button data-item-id="<?php echo $item->id; ?>" class="btn btn-mini delete-button"
                                type="button"><i class="icon-trash"></i></button>
                    <?php endif; ?>
                </td>
            <?php endif; ?>

        </tr>
    <?php endforeach; ?>
    </tbody>
</table>