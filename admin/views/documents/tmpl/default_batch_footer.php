<?php
/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;
?>
<a class="btn" type="button" onclick="document.getElementById('batch-category-id').value = '';document.getElementById('batch-client-id').value = '';document.getElementById('batch-language-id').value = ''" data-dismiss="modal">
    <?php echo JText::_('JCANCEL'); ?>
</a>
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('document.batch');">
    <?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
