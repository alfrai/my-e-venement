<?php use_helper('Date') ?>
<h1><?php echo __('Choose tickets') ?></h1></br>
<div id="event"><?php echo $manifestation->Event ?></div>
<div id="manifestation"><?php echo __('at') ?> <?php echo $manifestation->getFormattedDate() ?></div>
<div id="location"><?php echo __('location') ?> : <?php echo $manifestation->Location ?></div>
