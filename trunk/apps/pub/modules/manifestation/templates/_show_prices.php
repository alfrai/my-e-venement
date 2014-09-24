<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php $vel = sfConfig::get('app_tickets_vel') ?>
<?php if ( !isset($vel['full_seating_by_customer']) ) $vel['full_seating_by_customer'] = false; ?>
<?php use_helper('Number') ?>
<?php use_helper('Slug') ?>

<?php
  $sf_context->getEventDispatcher()->notify($event = new sfEvent($this, 'pub.before_showing_prices', array('manifestation' => $gauge->Manifestation)));
  if ( !$event->getReturnValue() )
  {
    echo $event['message'];
    return;
  }
?>

<?php
  // limitting the max quantity, especially for prices linked to member cards
  $vel = sfConfig::get('app_tickets_vel');
  $vel['max_per_manifestation'] = isset($vel['max_per_manifestation']) ? $vel['max_per_manifestation'] : 9;
  if ( $gauge->Manifestation->online_limit_per_transaction && $gauge->Manifestation->online_limit_per_transaction < $vel['max_per_manifestation'] )
    $vel['max_per_manifestation'] = $gauge->Manifestation->online_limit_per_transaction;
  
  // max per manifestation per contact ...
  $vel['max_per_manifestation_per_contact'] = isset($vel['max_per_manifestation_per_contact']) ? $vel['max_per_manifestation_per_contact'] : false;
  if ( $vel['max_per_manifestation_per_contact'] > 0 )
  {
    $max = $vel['max_per_manifestation_per_contact'];
    foreach ( $sf_user->getContact()->Transactions as $transaction )
    if ( $transaction->id != $sf_user->getTransaction()->id )
    foreach ( $transaction->Tickets as $ticket )
    if (( $ticket->transaction_id == $sf_user->getTransaction()->id || $ticket->printed_at || $ticket->integrated_at || $transaction->Order->count() > 0 )
      && !$ticket->hasBeenCancelled()
      && $gauge->Manifestation->id == $ticket->manifestation_id
    )
    {
      $vel['max_per_manifestation_per_contact']--;
    }
    $vel['max_per_manifestation'] = $vel['max_per_manifestation'] > $vel['max_per_manifestation_per_contact']
      ? $vel['max_per_manifestation_per_contact']
      : $vel['max_per_manifestation'];
  }
?>

<table class="prices">
<?php if ( $gauge->Manifestation->PriceManifestations->count() > 0 || $gauge->PriceGauges->count() > 0 ): ?>
<tbody>
<?php
  // WIP tickets
  $tickets = array();
  foreach ( $gauge->Tickets as $ticket )
  if ( !$ticket->price_id && $ticket->price_name )
    $tickets[] = $ticket;
?>
  <tr class="seating in-progress">
    <?php if ( $vel['full_seating_by_customer'] ): ?>
      <td class="seats" title="<?php echo $str = __('To remove a booked seat, click it on the plan') ?>">
        <?php include_partial('show_prices_seats', array('form' => $form, 'tickets' => $tickets)) ?>
      </td>
    <?php endif ?>
    <td class="price value explanation" colspan="2"><span><?php echo $str ?></span></td>
    <td class="quantity" title="<?php echo $str ?>"><?php echo count($tickets) ?></td>
    <td class="total">-</td>
  </tr>

<?php
  $pms = array();
  // priority to PriceGauge as it is in the model + ordering
  foreach ( array($gauge->PriceGauges, $gauge->Manifestation->PriceManifestations) as $data )
  foreach ( $data as $pm )
  if ( !isset($pms[$pm->price_id]) )
    $pms[$pm->price_id] = $pm;
  foreach ( $pms as $i => $pm )
  {
    unset($pms[$i]);
    $pms[str_pad($pm->value,10,'0',STR_PAD_LEFT).'|'.$pm->Price->name.'|'.$i] = $pm;
  }
  krsort($pms);
?>
<?php foreach ( $pms as $pm ): ?>
<?php if ( in_array($gauge->workspace_id, $pm->getRawValue()->Price->Workspaces->getPrimaryKeys()) ): ?>
  <?php
    // calculating the quantity of tickets already in the cart
    $tickets = array();
    foreach ( $pm->Price->Tickets as $ticket )
    if ( $ticket->gauge_id == $gauge->id )
      $tickets[] = $ticket;
    
    // price_id & default quantity
    $form
      ->setPriceId($pm->price_id)
      ->setQuantity(count($tickets));
    
    // limitting the max quantity, especially for prices linked to member cards
    $max = $gauge->value - $gauge->printed - $gauge->ordered - $gauge->Manifestation->online_limit - (sfConfig::get('project_tickets_count_demands',false) ? $gauge->asked : 0);
    $max = $max > $vel['max_per_manifestation'] ? $vel['max_per_manifestation'] : $max;
    
    // member cards limits
    if ( $pm->Price->member_card_linked )
    {
      $mc_max = 0;
      
      if ( isset($mcp[$pm->price_id]) )
      {
        $mc_max += $mcp[$pm->price_id][''] < 0 ? 0 : $mcp[$pm->price_id][''];
        if ( isset($mcp[$pm->price_id][$gauge->Manifestation->event_id]) )
          $mc_max += $mcp[$pm->price_id][$gauge->Manifestation->event_id];
      }
      
      if ( $max > $mc_max )
        $max = $mc_max;
    }
    
    if ( $max <= 0 )
    {
      echo '<tr><td class="price" colspan="'.($vel['full_seating_by_customer'] ? 6 : 5).'">'.__('Price %%price%% not available', array('%%price%%' => $pm->Price)).'</td></tr>';
      continue;
    }
    
    $form->setMaxQuantity($max);
  ?>
  <tr data-price-id="<?php echo $pm->price_id ?>">
    <?php if ( $vel['full_seating_by_customer'] ): ?>
      <td class="seats">
        <?php include_partial('show_prices_seats', array('form' => $form, 'tickets' => $tickets)) ?>
      </td>
    <?php endif ?>
    <td class="price">
      <?php echo $pm->Price->description ? $pm->Price->description : $pm->Price ?>
      <?php echo $form->renderHiddenFields() ?>
    </td>
    <td class="value"><?php echo format_currency($pm->value,'€') ?></td>
    <td class="quantity"><?php echo $form['quantity'] ?></td>
    <td class="total"><?php echo format_currency(0,'€') ?></td>
    <td class="extra-taxes"></td>
  </tr>
<?php endif ?>
<?php endforeach ?>
</tbody>
<?php endif ?>
<tfoot>
  <tr>
    <?php if ( $vel['full_seating_by_customer'] ): ?><td class="seats"></td><?php endif ?>
    <td class="price"></td>
    <td class="value"></td>
    <td class="quantity"></td>
    <td class="total"><?php echo format_currency(0,'€') ?></td>
    <td class="extra-taxes"></td>
  </tr>
  <tr>
    <?php if ( $vel['full_seating_by_customer'] ): ?>
    <?php use_javascript('pub-seated-plan?'.date('Ymd'),'last') ?>
    <?php use_javascript('helper?'.date('Ymd')) ?>
    <td class="seats"></td>
    <?php endif ?>
    <td colspan="5" class="submit">
      <input type="submit" name="submit" value="<?php echo __('Cart') ?>" />
    </td>
  </td>
</tfoot>
<thead>
  <tr>
    <?php if ( $vel['full_seating_by_customer'] ): ?><td class="seats"><?php echo __('Seats') ?></td><?php endif ?>
    <td class="price"><?php echo __('Price') ?></td>
    <td class="value"><?php echo __('Value') ?></td>
    <td class="quantity"><?php echo __('Quantity') ?></td>
    <td class="total"><?php echo __('Total') ?></td>
    <td class="extra-taxes"><?php echo __('Taxes') ?></td>
  </tr>
</thead>
</table>
