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
<div class="ui-widget-content ui-corner-all seats-allocation">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Seats allocation') ?> / <span class="transaction_id"><?php echo __('Transaction #%%id%%',array('%%id%%' => $transaction->id)) ?></span></h1>
  </div>
  <div class="ui-corner-all ui-widget-content ui-widget">

<p id="reload">
  <a href="javascript: location.reload();"
     class="fg-button ui-state-default fg-button-icon-right">
    <?php echo __('Force reloading the plan') ?>
  </a>
</p>
<p id="context">
  <span class="manifestation"><?php echo $transaction->Tickets[0]->Manifestation->getNameWithFullDate() ?></span>
  <span class="gauge"><?php echo $transaction->Tickets[0]->Gauge->Workspace ?></span>
</p>

<form action="<?php echo url_for('ticket/resetASeat?id='.$transaction->id) ?>" method="get" id="todo" class="reset-a-seat">
  <?php foreach ( $transaction->Tickets as $ticket ): ?>
  <span class="ticket" title="#<?php echo $ticket->id ?>">
    <?php echo $ticket->price_name ?>
    <input type="hidden" name="ticket_id" value="<?php echo $ticket->id ?>" />
  </span>
  <?php endforeach ?>
  <span class="total"><?php echo $transaction->Tickets->count() ?></span>
  <span style="display: none;">
    <input type="hidden" name="ticket[_csrf_token]" value="<?php $f = new sfForm; echo $f->getCSRFToken() ?>" />
    <input type="hidden" name="ticket[numerotation]" value="" />
    <input type="hidden" name="ticket[gauge_id]" value="<?php echo $transaction->Tickets[0]->gauge_id ?>" />
  </span>
</form>
<p id="arrow">&nbsp;↓</p>
<div id="done">
  <form action="<?php echo url_for('ticket/giveASeat?id='.$transaction->id) ?>" method="get">
    <p>
      <input type="hidden" name="ticket[_csrf_token]" value="<?php $f = new sfForm; echo $f->getCSRFToken() ?>" />
      <input type="hidden" name="ticket[id]" value="" />
      <input type="hidden" name="ticket[numerotation]" value="" />
      <span class="error_msg"><?php echo __('An error occurred during the seat allocation. Please try again.') ?></span>
    </p>
  </form>
  <span class="total">0</span>
</div>

<p id="plan"><a class="picture seated-plan" href="<?php echo cross_app_url_for('event', 'seated_plan/getSeats?id='.$seated_plan->id.'&gauge_id='.$gauge->id.'&transaction_id='.$transaction->id) ?>" style="background-color: <?php echo $seated_plan->background ?>;">
  <?php echo $seated_plan->getRaw('Picture')->getHtmlTag(array('title' => $seated_plan->Picture)) ?>
</a></p>

<p id="next">
  <a href="<?php echo $url_next ?>"
     class="fg-button ui-state-default fg-button-icon-right">
    <?php echo __('Next') ?>
  </a>
</p>

<script type="text/javascript">
$(document).ready(function(){
  document.seated_plan_functions.push(function()
  {
    var click;
    $('.seated-plan .seat.txt').click(click = function(){
      if ( $('#todo .ticket').length == 0 || $(this).is('.printed') || $(this).is('.ordered') )
        return false;
      
      var seat = this;
      $('#done form [name="ticket[numerotation]"]').val($(this).find('input').val());
      $('#done form [name="ticket[id]"]').val($('#todo .ticket:first input').val());
      $.ajax({
        url: $('#done form').prop('action'),
        data: $('#done form').serialize(),
        success: function(){
          $('#done form input[name="ticket[numerotation]"], #done form input[name="ticket[id]"]').val('');
          var id = $(seat).clone(true).removeClass('seat').removeClass('txt').attr('class');
          $('#todo .ticket:first').prependTo('#done');
          $('#todo .total').html(parseInt($('#todo .total').html())-1);
          $('#done .total').html(parseInt($('#todo .total').html())+1);
          $('.seated-plan .'+id).addClass('ordered');
          $(seat).addClass('in-progress').dblclick(seated_plan_unallocate_seat);
          
          // if there is no more ticket, go to the next step, including editting the order
          if ( $('#todo .ticket').length == 0 )
            window.location = $('#next a').prop('href');
        },
        error: function(){
          $('#done form input').val('');
          alert($('#done form .error_msg').html());
        }
      });
    });
    
    $('#menu li').unbind().addClass('disabled');
    $('#banner a, #footer a').prop('href','#').unbind().click(function(){ return false; });
  });
});
</script>

</div>
</div>
