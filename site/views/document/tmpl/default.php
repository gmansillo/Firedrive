<?php
/**
 * @package     Firedrive
 * @author      Giovanni Mansillo
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @copyright   Firedrive
 */
defined('_JEXEC') or die;

jimport('joomla.html.html.bootstrap');

$tparams = $this->item->params;
?>

<div class="document<?php echo $this->pageclass_sfx; ?>" itemscope itemtype="https://schema.org/Person">
	<?php if ($tparams->get('show_page_heading', 1)) : ?>
        <h1>
			<?php echo $this->escape($tparams->get('page_heading', 1)); ?>
        </h1>
	<?php endif; ?>

	<?php if ($this->doc->title && $tparams->get('show_title', 1)) : ?>
        <div class="page-header">
            <h2>
				<?php if ($this->item->state == 0) : ?>
                    <span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
				<?php endif; ?>
                <span class="document-name" itemprop="name"><?php echo $this->doc->title; ?></span>
            </h2>
        </div>
	<?php endif; ?>

	<?php $show_document_category = $tparams->get('show_document_category', 'show_with_link'); ?>

	<?php if ($show_document_category === 'show_no_link') : ?>
        <h3>
            <span class="document-category"><?php echo $this->doc->category_title; ?></span>
        </h3>
	<?php elseif ($show_document_category === 'show_with_link') : ?>
		<?php $docLink = FiredriveHelperRoute::getCategoryRoute($this->doc->catid); ?>
        <h3>
            <span class="document-category"><a href="<?php echo $docLink; ?>">
                    <?php echo $this->escape($this->doc->category_title); ?></a>
            </span>
        </h3>
	<?php endif; ?>

	<?php echo $this->item->event->afterDisplayTitle; ?>

	<?php // if ($tparams->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
	<?php // $this->item->tagLayout = new JLayoutFile('joomla.content.tags');  ?>
	<?php // echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
	<?php // endif; ?>

	<?php echo $this->item->event->beforeDisplayContent; ?>

	<?php if ($tparams->get('document_show_document_icon', 1) && !empty($this->doc->icon)) : ?>
        <p><img src="<?php echo $this->doc->icon; ?>" style="max-width:120px"/></p>
	<?php endif; ?>

	<?php if ($tparams->get('document_show_document_description', 1) && !empty($this->doc->description)) : ?>
        <dl class="dl-horizontal">
            <dt><?php echo JText::_('COM_FIREDRIVE_DESCRIPTION_LABEL'); ?></dt>
            <dd><?php echo $this->doc->description; ?></dd>
        </dl>
	<?php endif; ?>

	<?php if ($tparams->get('document_show_document_modified_by', 1) && !empty($this->doc->created_by)) : ?>
        <dl class="dl-horizontal">
            <dt><?php echo JText::_('COM_FIREDRIVE_CREATED_BY_LABEL'); ?></dt>
            <dd><?php echo $this->doc->created_by_name; ?></dd>
        </dl>
	<?php endif; ?>

	<?php if ($tparams->get('document_show_document_modified', 1) && !empty($this->doc->created)) : ?>
        <dl class="dl-horizontal">
            <dt><?php echo JText::_('COM_FIREDRIVE_CREATED_LABEL'); ?></dt>
            <dd><?php echo $this->doc->created; ?></dd>
        </dl>
	<?php endif; ?>

	<?php if ($tparams->get('document_show_document_modified', 1) && !empty($this->doc->modified) && $this->doc->modified != '0000-00-00 00:00:00') : ?>
        <dl class="dl-horizontal">
            <dt><?php echo JText::_('COM_FIREDRIVE_MODIFIED_LABEL'); ?></dt>
            <dd><?php echo $this->doc->modified; ?></dd>
        </dl>
	<?php endif; ?>

	<?php if ($tparams->get('document_show_document_file_size', 1) && !empty($this->doc->file_size)) : ?>
        <dl class="dl-horizontal">
            <dt><?php echo JText::_('COM_FIREDRIVE_FILE_SIZE_LABEL'); ?></dt>
            <dd><?php echo FiredriveHelper::convertToReadableSize($this->doc->file_size); ?></dd>
        </dl>
	<?php endif; ?>

	<?php if ($tparams->get('document_show_document_license', 1) && !empty($this->doc->license)) : ?>
        <dl class="dl-horizontal">
            <dt><?php echo JText::_('COM_FIREDRIVE_LICENSE_LABEL'); ?></dt>
            <dd>
				<?php if (!empty($this->doc->license_link)) : ?><a
                        href="<?php echo $this->doc->license_link; ?>"><?php endif; ?>
					<?php echo $this->doc->license; ?>
					<?php if (!empty($this->doc->license_link)) : ?></a><?php endif; ?>
            </dd>
        </dl>
	<?php endif; ?>

	<?php if ($tparams->get('document_show_document_version', 1) && !empty($this->doc->version)) : ?>
        <dl class="dl-horizontal">
            <dt><?php echo JText::_('COM_FIREDRIVE_VERSION_LABEL'); ?></dt>
            <dd><?php echo $this->doc->version; ?></dd>
        </dl>
	<?php endif; ?>

	<?php if ($tparams->get('document_show_md5', 1) && !empty($this->doc->md5hash)) : ?>
        <dl class="dl-horizontal">
            <dt><?php echo JText::_('COM_FIREDRIVE_MD5HASH_LABEL'); ?></dt>
            <dd><?php echo $this->doc->md5hash; ?></dd>
        </dl>
	<?php endif; ?>

	<?php // if ($tparams->get('show_user_custom_fields') && $this->docUser) : ?>
	<?php // echo $this->loadTemplate('user_custom_fields');  ?>
	<?php // endif; ?>

	<?php $downloadLink = JRoute::_('index.php?option=com_firedrive&amp;view=document&amp;id=' . $this->doc->id . '&amp;format=raw'); ?>

    <a href="<?php echo $downloadLink; ?>"
       class="btn btn-default"><?php echo JText::_('COM_FIREDRIVE_DOWNLOAD_BUTTON') ?></a>

	<?php echo $this->item->event->afterDisplayContent; ?>

</div>