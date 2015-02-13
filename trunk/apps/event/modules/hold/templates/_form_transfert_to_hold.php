<div class="sf_admin_form_row sf_admin_form_field_transfert_to_hold">
  <span class="hold_id">&nbsp;<?php
    $select = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Hold',
      'add_empty' => true,
      'query' => Doctrine::getTable('Hold')->createQuery('h')
        ->andWhere('h.id != ?', $form->getObject()->id)
        ->andWhere('h.manifestation_id = ?', $form->getObject()->manifestation_id),
      'order_by' => array('ht.name',''),
      'method' => 'getName',
    ));
    echo $select->render('hold_id');
  ?></span>
  <div class="label ui-helper-clearfix">
    <div class="help">
      <span class="ui-icon ui-icon-help floatleft"></span>
      <?php echo __('Transfert released seats into this hold, if no transaction is set for this purpose.') ?>
    </div>
  </div>
</div>
