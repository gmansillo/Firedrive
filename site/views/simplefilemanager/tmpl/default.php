<?php
    // no direct access
    defined('_JEXEC') or die;

    $canEdit = JFactory::getUser()->authorise('core.edit', 'com_simplefilemanager');
    if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_simplefilemanager'))
    {
        $canEdit = JFactory::getUser()->id == $this->item->created_by;
    }
?>

<h2>
    <?php echo $this->params->get('page_heading'); ?>
</h2>

<?php if ($this->item) : ?>

    <h2><?php echo $this->item->title; ?></h2>

    <?php if ($this->showIcon): ?>
        <p>
            <img alt="<?php echo $this->item->title; ?>" src="<?php echo $this->item->icon; ?>" class="sfm-icon" />
        </p>
    <?php endif; ?>

    <?php if ($this->showDesc && $this->item->description): ?>
        <p><?php echo $this->item->description; ?></p>
    <?php endif; ?>

    <div class="item_fields">
        <table class="table">

            <!-- <?php echo JText::_('JGLOBAL_FIELD_ID_LABEL'); ?>: <?php echo $this->item->id; ?>-->

            <?php if ($this->showDate): ?>
                <tr>
                    <th><?php echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_CREATION'); ?></th>
                    <td><?php echo JHTML::_('date', $this->item->file_created, JText::_('DATE_FORMAT_LC1')); ?></td>
                </tr>
            <?php endif; ?>

            <?php if ($this->showAuth): ?>
                <tr>
                    <th><?php echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_AUTHOR'); ?></th>
                    <td><?php echo JFactory::getUser($this->item->author)->name; ?></td>
                </tr>
            <?php endif; ?>


            <tr>
                <th><?php echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_CATEGORY'); ?></th>
                <td><?php echo $this->item->category_title; ?></td>
            </tr>

            <?php if ($this->showSize): ?>
                <tr>
                    <th><?php echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_SIZE'); ?></th>
                    <td><?php echo round($this->item->file_size * .0009765625, 2); ?>Kb</td>
                </tr>
            <?php endif; ?>

            <?php if ($this->showLicence && $this->item->license ): ?>
                <tr>
                    <th><?php echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_LICENSE'); ?></th>
                    <td><?php if ($this->item->license_link)
                        {
                            echo '<a href="' . $this->item->license_link . '" target="_blank">' . $this->item->license . '</a>';
                        }
                        else
                        {
                            echo $this->item->license;
                        } ?></td>
                </tr>
            <?php endif; ?>

            <?php if ($this->item->version): ?>
                <tr>
                    <th><?php echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_VERSION'); ?></th>
                    <td><?php echo $this->item->version; ?></td>
                </tr>
            <?php endif; ?>

            <?php if ($this->showMD5): ?>
                <tr>
                    <th><?php echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_MD5'); ?></th>
                    <td><?php echo $this->item->md5hash; ?></td>
                </tr>
            <?php endif; ?>

            <tr>
                <th><?php echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_FILE_ACCESS'); ?></th>
                <td>
                    <?php
                        switch ($this->item->visibility) {
                            case 1:
                                echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_VISIBILITY_PUBLIC');
                                break;
                            case 2:
                            case 5:
                                echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_VISIBILITY_REGISTRED');
                                break;
                            case 4:
                                echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_VISIBILITY_GROUP');
                                break;
                            case 3:
                                echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_VISIBILITY_USER');
                                break;
                            default:
                                break;
                        }

                    ?>
                </td>
            </tr>

        </table>
    </div>

    <div class="btn-group">
        <?php if ($this->item->canDownload): ?>
            <a class="btn btn-default" href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&view=download&id=' . (int)$this->item->id); ?>">
                <?php echo JText::_("COM_SIMPLEFILEMANAGER_DOWNLOAD_TEXT"); ?>
            </a>
        <?php endif; ?>
        <?php if ($canEdit && $this->item->checked_out == 0): ?>
            <a class="btn btn-default" href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&task=simplefilemanager.edit&id=' . $this->item->id); ?>"><?php echo JText::_("COM_SIMPLEFILEMANAGER_EDIT_ITEM"); ?></a>
        <?php endif; ?>
        <?php if (JFactory::getUser()->authorise('core.delete', 'com_simplefilemanager')): ?>
            <a class="btn btn-default" href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&task=simplefilemanager.remove&id=' . $this->item->id, false, 2); ?>"><?php echo JText::_("COM_SIMPLEFILEMANAGER_DELETE_ITEM"); ?></a>
        <?php endif; ?>
    </div>

<?php
    else:
        echo JText::_('COM_SIMPLEFILEMANAGER_ITEM_NOT_LOADED');
    endif;
?>
