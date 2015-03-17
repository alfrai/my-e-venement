<?php use_javascript('tck-touchscreen-prices?'.date('Ymd')) ?>
<a href="<?php echo url_for('ticket/cancelPartial') ?>"
   class="ui-icon-left cancel"
   target="_blank"
   title="<?php echo __('Cancel printed tickets as you click on prices now.') ?>">
  <span class="ui-icon ui-icon-extlink"></span>
  <?php echo __('Cancel printed tickets as you click on prices now.') ?>
</a>
<?php echo $form->renderFormTag(url_for('transaction/complete?id='.$transaction->id), array(
  'method' => 'get',
  'target' => '_blank',
  'autocomplete' => 'off',
  'class' => 'prices'
)) ?><p>
  <?php echo $form->renderHiddenFields() ?>
  <?php echo $form['qty']->render(array('pattern' => '-{0,1}\d+', 'title' => __('Positive or negative numbers only'), 'maxlength' => 4)) ?>
</p>
<?php if ( sfConfig::get('project_tickets_count_demands',false) ): ?>
<span class="count-demands"></span>
<?php endif ?>
</form>
<form action="<?php echo url_for('transaction/seatsFirst?id='.$transaction->id) ?>" target="_blank" class="seats-first" method="get">
  <button type="submit" name="gauge_id" value=""><?php echo __('Seats first') ?></button>
</form>
<form action="<?php echo url_for('transaction/dispatch') ?>" class="dispatch" method="get" title="<?php echo __("Check ticket's ids shown below any price") ?>">
  <input type="submit" name="prepare" value="<?php echo __('Prepare dispatching') ?>" />
  <input type="submit" name="dispatch" value="<?php echo __('Dispatch') ?>" />
</form>
