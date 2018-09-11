<?php
/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0));
JHtml::_('formbehavior.chosen', 'select');

if ($this->isNew)
    JFactory::getDocument()->addScriptDeclaration('
	jQuery(document).ready(function ($){
		var input = document.getElementById("jform_select_file");
		if(typeof input !== "undefined"){
                    input.classList.add("required");
                    input.attributes.required = "required";
		}
	});
');

$upload_field = $this->isNew ? "select_file" : "replace_file";
$max_file_size = FiredriveHelper::detectMaxUploadFileSize();

JFactory::getDocument()->addScriptDeclaration('

	Joomla.submitbutton = function(task)
	{
            if (task == "document.cancel") {
                Joomla.submitform(task, document.getElementById("document-form"));
            } else if (document.formvalidator.isValid(document.getElementById("document-form"))) {
                Joomla.submitform(task, document.getElementById("document-form"));
            }		
	};
        
        jQuery(document).ready(function ($){
            jQuery("#jform_' . $upload_field . '").on("change", function(e){ 
                var input = document.getElementById("jform_' . $upload_field . '");
                if (window.FileReader && typeof input !== "undefined" && input.files && typeof input.files[0] !== "undefined" && input.files[0].size > ' . $max_file_size . '){		
                    alert("' . JText::_('COM_FIREDRIVE_ALERT_MAX_FILE_SIZE_EXCEEDED') . '");
                    jQuery(this).val(null);
                }
            });
        });

');
?>

<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_firedrive&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="document-form" class="form-validate">

    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'document', JText::_('COM_FIREDRIVE_GROUP_LABEL_DOCUMENT')); ?>
        <div class="row-fluid">
            <div class="span9">
                <?php if (!$this->isNew): ?>
                    <div class="row-fluid">
                        <div class="span6">
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $this->form->getLabel('file_name'); ?>
                                </div>
                                <div class="controls selected">
                                    <div class="input-append">
                                        <?php echo $this->form->getInput('file_name'); ?>
                                        <a target="_blank" class="btn btn-primary" href="index.php?option=com_firedrive&task=download&id=<?php echo $this->item->id; ?>"><i class="icon-download">&nbsp;</i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="span6">
                            <?php echo $this->form->renderField($upload_field); ?>
                        </div>
                    </div>
                    <?php echo $this->form->renderField('fl_send_mail'); ?>				
                <?php else: ?>
                    <?php echo $this->form->renderField($upload_field); ?>
                <?php endif; ?>
                <?php echo $this->form->renderField('description'); ?>
            </div>			
            <div class="span3">
                <?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
                <div class="form-vertical">
                    <?php echo $this->form->renderField('visibility'); ?>
                    <?php echo $this->form->renderField('reserved_user'); ?>
                    <?php echo $this->form->renderField('reserved_group'); ?>
                </div>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_FIREDRIVE_GROUP_LABEL_DETAILS')); ?>
        <div class="row-fluid">
            <div class="span6">
                <?php echo $this->form->renderFieldset('details'); ?>
            </div>
            <div class="span6">
                <?php echo $this->form->renderFieldset('otherparams'); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6">
                <?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
            </div>
            <div class="span6">
                <?php echo $this->form->renderFieldset('metadata'); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
