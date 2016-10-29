<?php
/**
 *
 * @package     Simple File Manager
 * @author        Giovanni Mansillo
 *
 * @copyright   Copyright (C) 2005 - 2014 Giovanni Mansillo. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addScript('/media/com_simplefilemanager/js/chartist.min.js');
$document->addStyleSheet('/media/com_simplefilemanager/css/chartist.min.css');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
?>

<?php $db = JFactory::getDBO(); ?>

<div name="adminForm" id="adminForm">
    <?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <?php else : ?>
        <div id="j-main-container">
            <?php endif; ?>

            <fieldset class="adminform span12">

                <h5><?php echo JText::_('COM_SIMPLEFILEMANAGER_SUMMARY_DOWNLOAD_MOST'); ?></h5>

                <div class="row">

                    <div class="span12">

                        <?php

                        // Highest hits stats
                        $db->setQuery("SELECT download_counter, title FROM #__simplefilemanager ORDER BY download_counter DESC LIMIT 10");
                        $highestHits = $db->loadObjectList();

                        if (!empty($highestHits)):
                            ?>

                            <div class="ct-chart" id="last-week-stats"></div>

                            <script>
                                var data = {
                                    labels: [<?php foreach($highestHits as $h) echo "'".$h->title."', "; ?>],
                                    series: [
                                        [<?php foreach($highestHits as $h) echo "'".$h->download_counter."', "; ?>]
                                    ]
                                };
                                var options = {
                                    seriesBarDistance: 15,
                                    height: 200,
                                    axisY: {
                                        onlyInteger: true
                                    }
                                };
                                new Chartist.Bar('#last-week-stats', data, options);
                            </script>

                        <?php endif; ?>

                    </div>

                </div>

                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th width="70%"><?php echo JText::_('JFIELD_PARAMS_LABEL'); ?></th>
                        <th><?php echo JText::_('JSTATUS'); ?></th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    </tfoot>
                    <tbody>
                    <tr>
                        <td><?php echo JText::_('COM_SIMPLEFILEMANAGER_SUMMARY_DOWNLOAD_TOT'); ?></td>
                        <td>
                            <?php
                            $db->setQuery("SELECT SUM(download_counter) AS downloads FROM #__simplefilemanager");
                            echo $db->loadResult();
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo JText::_('COM_SIMPLEFILEMANAGER_SUMMARY_DOWNLOAD_LAST'); ?></td>
                        <td>
                            <?php
                            $res = $db->setQuery("SELECT MAX(download_last) AS last FROM #__simplefilemanager")->loadResult();
                            if ($res > 0) {
                                echo date(JText::_('DATE_FORMAT_LC3'), strtotime($res));
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo JText::_('COM_SIMPLEFILEMANAGER_SUMMARY_TOT'); ?></td>
                        <td>
                            <?php echo $db->setQuery("SELECT COUNT(*) FROM #__simplefilemanager")->loadResult(); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo JText::_('JPUBLISHED'); ?></td>
                        <td>
                            <?php echo $db->setQuery("SELECT COUNT(*) FROM #__simplefilemanager WHERE state = 1")->loadResult(); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo JText::_('JUNPUBLISHED'); ?></td>
                        <td>
                            <?php echo $db->setQuery("SELECT COUNT(*) FROM #__simplefilemanager WHERE state = 0")->loadResult(); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo JText::_('JARCHIVED'); ?></td>
                        <td>
                            <?php echo $db->setQuery("SELECT COUNT(*) FROM #__simplefilemanager WHERE state = 2")->loadResult(); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo JText::_('JTRASHED'); ?></td>
                        <td>
                            <?php echo $db->setQuery("SELECT COUNT(*) FROM #__simplefilemanager WHERE state = -2")->loadResult(); ?>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </fieldset>

        </div>
    </div>
    <script type="text/javascript">
        (function () {
            var submitbuttom = Joomla.submitbutton;
            Joomla.submitbutton = function (task) {
                if (task == 'reload') {
                    location.reload(true);
                    return false;
                } else {
                    return submitbutton.apply(this, arguments);
                }
            }
        })();
    </script>