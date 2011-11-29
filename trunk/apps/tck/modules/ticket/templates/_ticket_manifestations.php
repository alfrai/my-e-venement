  <?php use_helper('Date','Number') ?>
  <?php if ( sfConfig::get('app_transaction_gauge_alert') ): ?>
  <div id="force-alert"><?php echo __("Warning: you've got full gauges !") ?></div>
  <div id="gauge-alert">dummy</div>
  <?php endif ?>
  <form action="<?php echo url_for('ticket/manifs?id='.$transaction->id) ?>" method="post">
    <a href="<?php echo url_for('ticket/gauge') ?>" id="gauge_url"></a>
    <div class="gauge ui-widget-content ui-corner-all"></div>
    <p class="manif_new">
      <span class="title"><?php echo __('Manifestations') ?>:</span>
      <span>
        <input type="text" name="manif_new" value="" />
      </span>
      <a href="#" class="toggle_view"><?php echo __('hide / show') ?></a>
    </p>
    <ul class="manifestations_add ui-widget-content ui-corner-all">
    <?php foreach ( $manifestations_add as $manif ): ?>
      <li class="manif">
      <?php include_partial('ticket_manifestation',array(
        'manif' => $manif,
        'active' => false,
      )) ?>
      </li>
    <?php endforeach ?>
    </ul>
  </form>
