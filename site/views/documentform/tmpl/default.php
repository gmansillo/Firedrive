<?php
/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
// no direct access
defined('_JEXEC') or die();

JHtml::_('behavior.tabstate');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$doc = JFactory::getDocument();
$doc->addScript(JUri::base() . '/components/com_simplefilemanager/assets/js/form.js');
$doc->addScriptDeclaration('
	jQuery(document).ready(function ($){
		var input = document.getElementById("jform_select_file");
		if(typeof input !== "undefined"){
			input.classList.add("required");
		}
	});
');

$user         = JFactory::getUser();
$canManage    = $user->authorise('core.manage', 'com_simplefilemanager');
$canEditState = $user->authorise('core.edit.state', 'com_simplefilemanager');
?>

<div class="simplefilemanager-edit front-end-edit">

    <?php
    // TODO: Get page_intro from menu item params
// echo $this->params->get('page_intro'); 
    ?>

    <form   
        enctype="multipart/form-data"
        id="form-documentform"
        method="post"
        class="form-validate"
        action="<?php echo JRoute::_('index.php?option=com_simplefilemanager&task=simplefilemanager.save'); ?>"
        >
        <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>"/>

        <fieldset>

            <?php echo $this->form->renderField('title'); ?>

            <?php if ($this->item->params->get('default_category', "") == ""): ?> 
                <?php echo $this->form->renderField('catid'); ?>
            <?php endif; ?> 

            <?php echo $this->form->renderField('select_file'); ?>

            <?php echo $this->form->renderField('description'); ?>

        </fieldset>

        <button type="submit" class="btn button btn-primary">
            <span class="icon-ok"></span> <?php echo JText::_('JSUBMIT'); ?>
        </button>

        <a class="btn button btn-default"
           href="<?php echo JRoute::_('index.php?option=com_simplefilemanager'); ?>"
           title="<?php echo JText::_('JCANCEL'); ?>">
            <span class="icon-cancel"></span>
            <?php echo JText::_('JCANCEL'); ?>
        </a>

        <input type="hidden" name="MAX_FILE_SIZE" value="2000000">
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="option" value="com_simplefilemanager"/>
        <input type="hidden" name="task" value="documentform.save"/>
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
