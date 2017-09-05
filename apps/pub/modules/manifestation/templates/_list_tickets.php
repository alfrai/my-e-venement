<?php
  $vel = sfConfig::get('app_tickets_vel');
  if (!( isset($vel['display_tickets_in_manifestations_list']) && $vel['display_tickets_in_manifestations_list'] ))
    return;
  
  $limit_prices = array();
  if ( $sf_request->hasParameter('mc_pending') )
  {
    foreach ( $sf_user->getTransaction()->MemberCards as $mc )
    foreach ( $mc->MemberCardPrices as $mcp )
    if ( $mcp->event_id == $manifestation->event_id )
      $limit_prices[] = $mcp->price_id;
  }
?>

<?php
  // limitting the max quantity, especially for prices linked to member cards
  $vel['max_per_manifestation'] = isset($vel['max_per_manifestation']) ? $vel['max_per_manifestation'] : 9;
  if ( $manifestation->online_limit_per_transaction && $manifestation->online_limit_per_transaction < $vel['max_per_manifestation'] )
    $vel['max_per_manifestation'] = $manifestation->online_limit_per_transaction;
?>
<?php $hour = $this->manifestation->Event->close_before;?>
<?php
    if (!$hour) 
    {
        $delay = sfConfig::get('app_tickets_close_before','36 hours');
    }
    else 
    {
        $delay = $hour.' hours';
    }
?>
<?php if ( strtotime('now + '.$delay) > strtotime($manifestation->happens_at) ): ?>
  <?php echo nl2br(pubConfiguration::getText('app_texts_manifestation_closed')) ?>
<?php else: ?>
<?php use_helper('Number') ?>
<ul><?php foreach ( $manifestation->Gauges as $gauge ): ?>
  <?php
    // max by gauge
    $gauge = Doctrine::getTable('Gauge')->find($gauge->id);
    $max = $gauge->value - $gauge->printed - $gauge->ordered - (!(isset($vel['no_online_limit_from_manifestations']) && $vel['no_online_limit_from_manifestations']) ? $manifestation->online_limit : 0) - (sfConfig::get('project_tickets_count_demands',false) ? $gauge->asked : 0);
    $max = $max > $vel['max_per_manifestation'] ? $vel['max_per_manifestation'] : $max;
  ?>
  <?php if ( $max > 0 ): ?>
  <li data-gauge-id="<?php echo $gauge->id ?>">
    <span class="gauge-name"><?php echo $manifestation->Gauges->count() > 1 ? $gauge : '' ?></span>
    <?php
      $prices = array();
      foreach ( $manifestation->PriceManifestations as $pm )
      if ( !$limit_prices || in_array($pm->price_id, $limit_prices) )
      if ( $pm->Price->isAccessibleBy($sf_user->getRawValue()) )
        $prices[$pm->price_id] = $pm;
      if ( $gauge->getTable()->hasRelation('PriceGauges') )
      foreach ( $gauge->PriceGauges as $pg )
      if ( !$limit_prices || in_array($pm->price_id, $limit_prices) )
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
      <?php
        if ( $price->Price->member_card_linked )
        {
          $mc_max = 0;
          $mcs = new Doctrine_Collection('MemberCard');
          if ( $sf_user->getTransaction()->contact_id )
            $mcs->merge($sf_user->getContact()->MemberCards->getRawValue());
          $mcs->merge($sf_user->getTransaction()->MemberCards->getRawValue());
          foreach ( $mcs as $mc )
          foreach ( $mc->MemberCardPrices as $mcp )
          if ( $mcp->price_id == $id && ($mcp->event_id == $manifestation->event_id || is_null($mcp->event_id)) )
            $mc_max++;
          $max = $max > $mc_max ? $mc_max : $max;
        }
      ?>
      <?php if ( ! $price instanceof Doctrine_Record ) $price = $price->getRawValue(); ?>
      <?php if ( in_array($gauge->workspace_id, $price->Price->Workspaces->getPrimaryKeys()) ): ?>
      <?php
        $form = new PricesPublicForm;
        $form->setGaugeId($gauge->id);
        $form->setPriceId($id);
      ?>
      <?php if ( $max > 0 ): ?>
      <li data-price-id="<?php echo $id ?>"><form action="<?php echo url_for('ticket/commit') ?>" method="get">
        <span class="name" title="<?php echo $txt = $price->Price->description ? $price->Price->description : $price->Price ?>"><?php echo $txt ?></span>
        <span class="value"><?php echo format_currency($price->value, $sf_context->getConfiguration()->getCurrency()) ?></span>
        <span class="qty"><input
          type="number"
          name="price[<?php echo $gauge->id ?>][<?php echo $id ?>][quantity]"
          min="0"
          max="<?php echo $max ?>"
          value="<?php echo $tickets[$id] ?>"
        /></span>
        <span class="data">
          <?php echo $form->renderHiddenFields() ?>
          <input type="hidden" name="no_redirect" value="1" />
        </span>
        <span class="total <?php echo $max == 0 ? 'n-a' : '' ?>"></span>
      </form></li>
      <?php endif ?>
      <?php endif ?>
    <?php endforeach ?></ul>
  </li>
  <?php else: ?>
    <li><?php include_partial('show_full') ?></li>
  <?php endif ?>
<?php endforeach ?></ul>
<?php endif ?>
