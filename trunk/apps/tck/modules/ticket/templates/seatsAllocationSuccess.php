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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php use_helper('CrossAppLink') ?>
<?php use_javascript('tck-seated-plan') ?>
<div class="ui-widget-content ui-corner-all seats-allocation">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Seats allocation') ?> / <span class="transaction_id"><?php echo __('Transaction #%%id%%',array('%%id%%' => $transaction->id)) ?></span></h1>
  </div>
  <div class="ui-corner-all ui-widget-content ui-widget">

<p class="first next">
  <?php $opt = sfConfig::get('app_transaction_seated_plan', array()) ?>
  <?php if ( !isset($opt['auto_next']) ) $opt['auto_next'] = true; ?>
  <a href="<?php echo $url_next ?>" onclick="javascript: window.opener.LI.initContent();"
     class="fg-button ui-state-default fg-button-icon-right <?php echo $opt['auto_next'] ? 'auto-click' : '' ?>">
    <?php echo __('Next') ?>
  </a>
</p>

<p id="reload">
  <a href="javascript: location.reload();"
     class="fg-button ui-state-default fg-button-icon-right">
    <?php echo __('Force reloading the plan') ?>
  </a>
</p>
<p id="context">
  <span class="manifestation"><?php echo $manifestation->getNameWithFullDate() ?></span>
  <span class="gauge"><?php echo $gauge->Workspace ?></span>
</p>

<div id="todo">
  <?php $nb = 0 ?>
  <?php foreach ( $transaction->Tickets as $ticket ): ?>
  <?php if ( !$ticket->numerotation ): $nb++; ?>
  <span class="ticket" title="#<?php echo $ticket->id ?>">
    <?php echo $ticket->price_name ?>
    <input type="hidden" name="ticket_id" value="<?php echo $ticket->id ?>" />
    <input type="hidden" name="ticket_numerotation" value="" />
  </span>
  <?php endif ?>
  <?php endforeach ?>
  <span class="total"><?php echo $nb ?></span>
  <form action="<?php echo url_for('ticket/resetASeat?id='.$transaction->id) ?>" method="get" class="reset-a-seat">
    <span style="display: none;">
      <input type="hidden" name="ticket[_csrf_token]" value="<?php $f = new sfForm; echo $f->getCSRFToken() ?>" />
      <input type="hidden" name="ticket[numerotation]" value="" />
      <input type="hidden" name="ticket[gauge_id]" value="<?php echo $gauge->id ?>" />
      <input type="hidden" name="ticket[transaction_id]" value="<?php echo $transaction->id ?>" />
    </span>
  </form>
</div>
<p id="arrow">&nbsp;↓</p>
<div id="done">
  <form action="<?php echo url_for('ticket/giveASeat?id='.$transaction->id) ?>" method="get">
    <p>
      <input type="hidden" name="ticket[_csrf_token]" value="<?php $f = new sfForm; echo $f->getCSRFToken() ?>" />
      <input type="hidden" name="ticket[id]" value="" />
      <input type="hidden" name="ticket[numerotation]" value="" />
      <input type="hidden" name="ticket[gauge_id]" value="<?php echo $gauge->id ?>" />
      <span class="error_msg"><?php echo __('An error occurred during the seat allocation. Please try again.') ?></span>
    </p>
  </form>
  <?php $nb = 0 ?>
  <?php foreach ( $transaction->Tickets as $ticket ): ?>
  <?php if ( $ticket->numerotation ): $nb++; ?>
  <span class="ticket" title="#<?php echo $ticket->id ?>">
    <?php echo $ticket->price_name ?>
    <input type="hidden" name="ticket_id" value="<?php echo $ticket->id ?>" />
    <input type="hidden" name="ticket_numerotation" value="<?php echo $ticket->numerotation ?>" />
  </span>
  <?php endif ?>
  <?php endforeach ?>
  <span class="total"><?php echo $nb ?></span>
</div>

<p id="plan"><a class="picture seated-plan" href="<?php echo cross_app_url_for('event', 'seated_plan/getSeats?id='.$seated_plan->id.'&gauge_id='.$gauge->id.'&transaction_id='.$transaction->id) ?>" style="background-color: <?php echo $seated_plan->background ?>;">
  <?php use_stylesheet('/private/event-seated-plan?'.date('Ymd')) ?>
  <?php echo $seated_plan->getRaw('Picture')->getHtmlTag(array(
    'title' => $seated_plan->Picture,
    'width' => $seated_plan->ideal_width,
  )) ?>
</a></p>

<p class="last next">
  <?php $opt = sfConfig::get('app_transaction_seated_plan', array()) ?>
  <?php if ( !isset($opt['auto_next']) ) $opt['auto_next'] = true; ?>
  <a href="<?php echo $url_next ?>" onclick="javascript: window.opener.LI.initContent();"
     class="fg-button ui-state-default fg-button-icon-right <?php echo $opt['auto_next'] ? 'auto-click' : '' ?>">
    <?php echo __('Next') ?>
  </a>
</p>

</div>
</div>
