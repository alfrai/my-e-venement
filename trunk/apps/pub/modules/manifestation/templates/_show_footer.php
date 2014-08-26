<div class="manifestation_bottom">
  <?php include_partial('show_named_tickets', array('manifestation' => $manifestation)) ?>
</div>
<div class="text_config manifestation_bottom">
  <?php echo nl2br(sfConfig::get('app_texts_manifestation_bottom')) ?>
</div>
<div class="manifestation_bottom">
<?php include_partial('event/description', array('manifestation' => $manifestation)) ?>
</div>
