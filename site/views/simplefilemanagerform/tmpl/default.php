<?php

// no direct access
defined('_JEXEC') or die();

JHtml::_('behavior.tabstate');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('formbehavior.chosen', 'select');

// Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_simplefilemanager', JPATH_ADMINISTRATOR);
$doc = JFactory::getDocument();
$doc->addScript(JUri::base() . '/components/com_simplefilemanager/assets/js/form.js');

?>

<script type="text/javascript">
    if (jQuery === 'undefined') {
        document.addEventListener("DOMContentLoaded", function (event) {
            jQuery('#form-simplefilemanager').submit(function (event) {

            });


        });
    } else {
        jQuery(document).ready(function () {
            jQuery('#form-simplefilemanager').submit(function (event) {

            });


        });
    }
</script>

<div class="simplefilemanager-edit front-end-edit">

    <form enctype="multipart/form-data" id="form-simplefilemanager"
          action="<?php echo JRoute::_('index.php?option=com_simplefilemanager&task=simplefilemanager.save'); ?>"
          method="post" class="form-validate"
          enctype="multipart/form-data">

        <input type="hidden" name="jform[id]"
               value="<?php echo $this->item->id; ?>"/> <input type="hidden"
                                                               name="jform[ordering]"
                                                               value="<?php echo $this->item->ordering; ?>"/>

        <input type="hidden" name="jform[state]" value="1"/>
        <input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>"/>
        <input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>"/>

        <?php if (empty($this->item->author)): ?>
            <input type="hidden" name="jform[author]" value="<?php echo JFactory::getUser()->id; ?>"/>
        <?php else: ?>
            <input type="hidden" name="jform[author]" value="<?php echo $this->item->author; ?>"/>
        <?php endif; ?>

        <?php if (empty($this->item->created_by)): ?>
            <input type="hidden" name="jform[created_by]" value="<?php echo JFactory::getUser()->id; ?>"/>
        <?php else: ?>
            <input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>"/>
        <?php endif; ?>

        <div class="control-group">
            <div class="controls">
                <button type="submit" class="btn btn-primary">
                    <span class="icon-ok"></span> <?php echo JText::_('JSUBMIT'); ?>
                </button>
                <a class="btn"
                   href="<?php echo JRoute::_('index.php?option=com_simplefilemanager'); ?>"
                   title="<?php echo JText::_('JCANCEL'); ?>">
                    <span class="icon-cancel"></span>
                    <?php echo JText::_('JCANCEL'); ?>
                </a>
            </div>
        </div>

        <fieldset>

            <ul class="nav nav-tabs">
                <li class="active"><a href="#general"
                                      data-toggle="tab"><?php echo JText::_('COM_SIMPLEFILEMANAGER_DOCUMENT_GENERAL') ?></a>
                </li>
                <li><a href="#publishing"
                       data-toggle="tab"><?php echo JText::_('COM_SIMPLEFILEMANAGER_DOCUMENT_PUBLISHING') ?></a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="general">

                    <?php echo $this->form->renderField('title'); ?>
                    <?php echo $this->form->renderField('catid'); ?>
                    <?php if (!empty($this->item->id)): ?>
                        <div class="control-group">
                            <div
                                class="control-label"><?php echo JText::_('COM_SIMPLEFILEMANAGER_SELECTED_FILE'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('file_name'); ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="control-group">
                        <div class="control-label">
                            <?php echo ($this->item->id == 0) ? $this->form->getLabel('file_size') : JText::_('COM_SIMPLEFILEMANAGER_FILE_CHANGE'); ?>
                        </div>
                        <div class="controls">
                            <input type="file" name="jform1[test][]"/>
                        </div>
                    </div>
                    <?php echo $this->form->renderField('description'); ?>

                </div>
                <div class="tab-pane" id="publishing">

                    <?php echo $this->form->renderField('icon'); ?>
                    <?php echo $this->form->renderField('version'); ?>
                    <?php echo $this->form->renderField('license'); ?>
                    <?php echo $this->form->renderField('license_link'); ?>

                    <?php if ($this->item->id > 0): ?>
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('id'); ?></div>
                        </div>
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('file_created'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('file_created'); ?></div>
                        </div>
                        <div class="control-group">
                            <div
                                class="control-label"><?php echo JText::_('COM_SIMPLEFILEMANAGER_FIELD_FILESIZE_LABEL'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('file_size'); ?></div>
                        </div>
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('file_name'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('file_name'); ?></div>
                        </div>
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('download_counter'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('download_counter'); ?></div>
                        </div>
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('download_last'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('download_last'); ?></div>
                        </div>
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('md5hash'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('md5hash'); ?></div>
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </fieldset>


        <input type="hidden" name="MAX_FILE_SIZE" value="2000000"> <input
            type="hidden" name="task" value=""/>


        <input type="hidden" name="option" value="com_simplefilemanager"/> <input
            type="hidden" name="task" value="simplefilemanagerform.save"/>
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
