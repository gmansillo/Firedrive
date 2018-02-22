<?php
/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0 ));
JHtml::_('formbehavior.chosen', 'select');

if($this->isNew) JFactory::getDocument()->addScriptDeclaration('
	jQuery(document).ready(function ($){
		var input = document.getElementById("jform_select_file");
		if(typeof input !== "undefined"){
			input.classList.add("required");
		}
	});
');

$input_field_id = $this->isNew ? "jform_select_file":"jform_replace_file";
$max_file_size = SimpleFileManagerHelper::detectMaxUploadFileSize();

JFactory::getDocument()->addScriptDeclaration('

	Joomla.submitbutton = function(task)
	{
		if (task == "document.cancel") {
			Joomla.submitform(task, document.getElementById("document-form"));
		} else if (document.formvalidator.isValid(document.getElementById("document-form"))) {	
				
			// File size checking
			var input = document.getElementById("'.$input_field_id.'");
			if (window.FileReader && typeof input !== "undefined" && input.files && typeof input.files[0] !== "undefined" && input.files[0].size > ' . $max_file_size . '){		
				if(!confirm("'.JText::_('COM_SIMPLEFILEMANAGER_MAX_FILE_SIZE_EXCEEDED_CONFIRMATION').'")) {
					 return false;
				}	
			}

			Joomla.submitform(task, document.getElementById("document-form"));
		}
				
	};

	// TODO: Delete this unuseful part
// 	jQuery(document).ready(function ($){
// 		$("#jform_type").on("change", function (a, params) {

// 			var v = typeof(params) !== "object" ? $("#jform_type").val() : params.selected;

// 			var img_url = $("#image, #url");
// 			var custom  = $("#custom");

// 			switch (v) {
// 				case "0":
// 					// Image
// 					img_url.show();
// 					custom.hide();
// 					break;
// 				case "1":
// 					// Custom
// 					img_url.hide();
// 					custom.show();
// 					break;
// 			}
// 		}).trigger("change");
// 	});

');
?>

<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_simplefilemanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="document-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'document', JText::_('COM_SIMPLEFILEMANAGER_GROUP_LABEL_DOCUMENT')); ?>
		<div class="row-fluid">
			<div class="span9">
				<?php if(!$this->isNew): ?>
				<div class="row-fluid">
					<div class="span6">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('file_name'); ?>
							</div>
							<div class="controls selected">
								<div class="input-append">
									<?php echo $this->form->getInput('file_name'); ?>
									<a target="_blank" class="btn btn-primary"
									href="index.php?option=com_simplefilemanager&task=download&id=<?php echo $this->item->id; ?>"><i
												class="icon-download">&nbsp;</i></a>
								</div>
							</div>
						</div>
					</div>
					<div class="span6">
						<?php echo $this->form->renderField('replace_file'); ?>
					</div>
				</div>
				<?php echo $this->form->renderField('fl_send_mail'); ?>				
				<?php else: ?>
					<?php echo $this->form->renderField('select_file'); ?>
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

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_SIMPLEFILEMANAGER_GROUP_LABEL_DETAILS')); ?>
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
