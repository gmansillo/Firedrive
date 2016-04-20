<?php
/**
 *
 * @package     Simple File Manager
 * @author       Giovanni Mansillo
 *
 * @copyright   Copyright (C) 2005 - 2014 Giovanni Mansillo. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.formvalidation');
require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/simplefilemanager.php');

$isNew       = $this->item->id == 0;
$maxFileSize = SimplefilemanagerHelper::getMaxFileUploadSize();

?>

<script type="text/javascript">
    jQuery(function () {
        jQuery('#jform1_uploader').change(function () {
            var f = this.files[0];
            var s = (f.size || f.fileSize);
            if (<?php echo $maxFileSize ?> <
            s
            )
            alert("<?php echo JText::sprintf('COM_SIMPLEFILEMANAGER_UPLOAD_MAX_SIZE_EXCEEDED_ERROR', $maxFileSize/1048576 ); ?>");
        })
    })

    Joomla.submitbutton = function (task) {
        if (task == 'simplefilemanager.cancel') {
            submitform(task);
        } else {
            var f = document.adminForm;
            if (document.formvalidator.isValid(f)) {
                submitform(task);
            } else {
                return false;
            }
        }
    }
</script>

<form
    enctype="multipart/form-data"
    action="<?php echo JRoute::_('index.php?option=com_simplefilemanager&layout=edit&id=' . (int)$this->item->id); ?>"
    method="post"
    name="adminForm"
    id="adminForm"
    class="form-validate"
    >

    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_SIMPLEFILEMANAGER_EDIT_LABEL_GENERAL', true)); ?>

        <div class="row-fluid">
            <div class="span9">
                <div class="row-fluid form-horizontal-desktop">

                    <?php if (!$isNew): ?>

                        <div class="span6">
                            <div class="control-group">
                                <div class="control-label"><?php echo $this->form->getLabel('file_name'); ?></div>
                                <div class="controls selected">
                                    <div class="input-append">
                                        <?php echo $this->form->getInput('file_name'); ?>
                                        <a target="_blank" class="btn btn-primary"
                                           href="index.php?option=com_simplefilemanager&view=download&id=<?php echo $this->item->id; ?>"><i
                                                class="icon-download">&nbsp;</i></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endif; ?>

                    <div class="span6">
                        <div class="control-group">
                            <div class="control-label">
                                <label id="jform1_uploader-lbl" for="jform1_uploader" class="required">
                                    <?php echo $isNew ? JText::_('COM_SIMPLEFILEMANAGER_SELECT_FILE') : JText::_('COM_SIMPLEFILEMANAGER_FILE_CHANGE'); ?>
                                    <span class="star">&nbsp;*</span>
                                </label>
                            </div>
                            <div class="controls choose">
                                <input type="file" <?php if ($isNew)
                                {
                                    echo 'class="required" aria-require="true" required="required"';
                                } ?> id="jform1_uploader" name="jform1[test][]"/>
                            </div>
                        </div>
                    </div>

                </div>

                <script>
                    $(document).ready(function () {
                        $('#jform1_uploader').on('change', function (evt) {
                            if (<?php echo SimplefilemanagerHelper::getMaxFileUploadSize(); ?> >
                            this.files[0].size
                            )
                            {
                                alert("casso!");
                            }
                        })
                    });
                </script>

                <div class="row-fluid form-horizontal-desktop">
                    <div class="span6">
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('license'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('license'); ?></div>
                        </div>
                    </div>

                    <div class="span6">
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('license_link'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('license_link'); ?></div>
                        </div>
                    </div>

                </div>

                <?php if (!$isNew): ?>

                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('fl_send_mail'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('fl_send_mail'); ?></div>
                    </div>

                <?php endif; ?>

                <div class="form-vertical">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('description'); ?></div>
                    </div>
                </div>

            </div>
            <div class="span3">
                <fieldset class="form-vertical">
                    <?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('version'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('version'); ?></div>
                    </div>

                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('author'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('author'); ?></div>
                    </div>

                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('notes'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('notes'); ?></div>
                    </div>
                </fieldset>
            </div>
        </div>

        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_SIMPLEFILEMANAGER_EDIT_LABEL_VISIBILITY', true)); ?>

        <div class="control-group">
            <div class="control-label"><?php echo $this->form->getLabel('visibility'); ?></div>
            <div class="controls"><?php echo $this->form->getInput('visibility'); ?></div>
        </div>
        <div class="control-group">
            <div class="control-label"><?php echo $this->form->getLabel('reserved_user'); ?></div>
            <div class="controls"><?php echo $this->form->getInput('reserved_user'); ?></div>
        </div>

        <div class="control-group">
            <div class="control-label"><?php echo $this->form->getLabel('reserved_group'); ?></div>
            <div class="controls"><?php echo $this->form->getInput('reserved_group'); ?></div>
        </div>

        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'advanced', JText::_('COM_SIMPLEFILEMANAGER_EDIT_LABEL_ADVANCED', true)); ?>

        <div class="control-group">
            <div class="control-label"><?php echo $this->form->getLabel('icon'); ?></div>
            <div class="controls"><?php echo $this->form->getInput('icon'); ?></div>
        </div>

        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php if ($this->item->id > 0): ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'info', JText::_('COM_SIMPLEFILEMANAGER_EDIT_LABEL_INFO', true)); ?>

            <div class="row-fluid form-horizontal-desktop">
                <div class="span6">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('file_size'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('file_size'); ?></div>
                    </div>

                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('file_created'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('file_created'); ?></div>
                    </div>

                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('md5hash'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('md5hash'); ?></div>
                    </div>
                </div>

                <div class="span6">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('download_counter'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('download_counter'); ?></div>
                    </div>

                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('download_last'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('download_last'); ?></div>
                    </div>
                </div>
            </div>

            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php endif; ?>

        <input type="hidden" name="MAX_FILE_SIZE" value="2000000">
        <input type="hidden" name="task" value=""/>
        <?php echo JHtml::_('form.token'); ?>

    </div>
</form>

