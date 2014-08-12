<div class="ranks_explanation">
  <p>glop glop glop glop</p>
  <a href="<?php echo url_for('seated_plan/batchSeatSetRank') ?>" style="display: none;"></a>
</div>
<?php include_partial('form_ranks_field', array(
  'name'  => 'id',
  'value' => $form->getObject()->id,
  'type'  => 'hidden',
)) ?>
