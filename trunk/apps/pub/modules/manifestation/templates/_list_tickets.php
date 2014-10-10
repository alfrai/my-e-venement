<?php
  // limitting the max quantity, especially for prices linked to member cards
  $vel = sfConfig::get('app_tickets_vel');
  $vel['max_per_manifestation'] = isset($vel['max_per_manifestation']) ? $vel['max_per_manifestation'] : 9;
  if ( $manifestation->online_limit_per_transaction && $manifestation->online_limit_per_transaction < $vel['max_per_manifestation'] )
    $vel['max_per_manifestation'] = $manifestation->online_limit_per_transaction;
?>

<?php if ( strtotime('now + '.sfConfig::get('app_tickets_close_before','36 hours')) > strtotime($manifestation->happens_at) ): ?>
  <?php echo nl2br(sfConfig::get('app_texts_manifestation_closed')) ?>
<?php else: ?>
<?php use_helper('Number') ?>
<ul><?php foreach ( $manifestation->Gauges as $gauge ): ?>
  <?php
    $gauge = Doctrine::getTable('Gauge')->find($gauge->id);
    $max = $gauge->value - $gauge->printed - $gauge->ordered - $manifestation->online_limit - (sfConfig::get('project_tickets_count_demands',false) ? $gauge->asked : 0);
    $max = $max > $vel['max_per_manifestation'] ? $vel['max_per_manifestation'] : $max;
  ?>
  <li data-gauge-id="<?php echo $gauge->id ?>">
    <span class="gauge-name"><?php echo $manifestation->Gauges->count() > 1 ? $gauge : '' ?></span>
    <?php
      $prices = array();
      foreach ( $manifestation->PriceManifestations as $pm )
      if ( $pm->Price->isAccessibleBy($sf_user->getRawValue()) )
        $prices[$pm->price_id] = $pm;
      if ( $gauge->getTable()->hasRelation('PriceGauges') )
      foreach ( $gauge->PriceGauges as $pg )
      if ( $pg->Price->isAccessibleBy($sf_user->getRawValue()) )
        $prices[$pg->price_id] = $pg;
      
      $order = array();
      foreach ( $prices as $id => $price )
        $order[$id] = $price->value.' '.($price->Price->description ? $price->Price->description : $price->Price);
      arsort($order);
      $tmp = array();
      foreach ( $order as $id => $value )
        $tmp[$id] = $prices[$id];
      $prices = $tmp;
      
      $tickets = array();
      foreach ( $prices as $id => $price )
        $tickets[$id] = 0;
      foreach ( $sf_user->getTransaction()->Tickets as $ticket )
      if ( $ticket->gauge_id == $gauge->id )
      {
        if ( isset($tickets[$ticket->price_id]) )
          $tickets[$ticket->price_id]++;
      }
    ?>
    <ul><?php foreach ( $prices as $id => $price ): ?>
      <?php if ( ! $price instanceof Doctrine_Record ) $price = $price->getRawValue(); ?>
      <?php if ( in_array($gauge->workspace_id, $price->Price->Workspaces->getPrimaryKeys()) ): ?>
      <?php
        $form = new PricesPublicForm;
        $form->setGaugeId($gauge->id);
        $form->setPriceId($id);
      ?>
      <li data-price-id="<?php echo $id ?>"><form action="<?php echo url_for('ticket/commit') ?>" method="get">
        <span class="name" title="<?php echo $txt = $price->Price->description ? $price->Price->description : $price->Price ?>"><?php echo $txt ?></span>
        <span class="value"><?php echo format_currency($price->value, '€') ?></span>
        <span class="qty"><?php if ( $max > 0 ): ?><input
          type="number"
          name="price[<?php echo $gauge->id ?>][<?php echo $id ?>][quantity]"
          min="0"
          max="<?php echo $max ?>"
          value="<?php echo $tickets[$id] ?>"
        /><?php else: ?>-<?php endif ?></span>
        <span class="data">
          <?php echo $form->renderHiddenFields() ?>
          <input type="hidden" name="no_redirect" value="1" />
        </span>
        <span class="total <?php echo $max == 0 ? 'n-a' : '' ?>"></span>
      </form></li>
      <?php endif ?>
    <?php endforeach ?></ul>
  </li>
<?php endforeach ?></ul>
<?php endif ?>
