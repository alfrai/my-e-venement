  <?php use_helper('Date','Number') ?>
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
