<?php
/**
 * @package     Simple File Manager
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 */
defined('_JEXEC') or die;

if (!key_exists('field', $displayData)) {
    return;
}

$field     = $displayData['field'];
$label     = JText::_($field->label);
$value     = $field->value;
$class     = $field->params->get('render_class');
$showLabel = $field->params->get('showlabel');

if ($field->context == 'com_simplefilemanager.mail') {
    // Prepare the value for the document form mail
    $value = html_entity_decode($value);

    echo ($showLabel ? $label . ': ' : '') . $value . "\r\n";
    return;
}

if (!$value) {
    return;
}
?>
<dt class="document-field-entry <?php echo $class; ?>">
    <?php if ($showLabel == 1) : ?>
        <span class="field-label"><?php echo htmlentities($label, ENT_QUOTES | ENT_IGNORE, 'UTF-8'); ?>: </span>
    <?php endif; ?>
</dt>
<dd class="document-field-entry <?php echo $class; ?>">
    <span class="field-value"><?php echo $value; ?></span>
</dd>
