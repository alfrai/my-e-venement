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
<div class="ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Seats allocation') ?></h1>
  </div>
  <div class="ui-corner-all ui-widget-content ui-widget">

<h2 id="transaction_id"><?php echo __('Transaction #%%id%%',array('%%id%%' => $transaction->id)) ?></h2>

<p id="todo">
  <?php foreach ( $transaction->Tickets as $ticket ): ?>
    <span class="ticket" title="#<?php echo $ticket->id ?>"><?php echo $ticket->price_name ?></span>
  <?php endforeach ?>
</p>
<p id="arrow">&nbsp;↓</p>
<p id="done">
  &nbsp;
</p>

<p><a class="picture seated-plan" href="<?php echo cross_app_url_for('event', 'seated_plan/getSeats?id='.$seated_plan->id.'&gauge_id='.$gauge->id) ?>" style="background-color: <?php echo $seated_plan->background ?>;">
  <?php echo $seated_plan->getRaw('Picture')->getHtmlTag(array('title' => $seated_plan->Picture)) ?>
</a></p>

<p class="next">
  <a href="<?php echo $url_next ?>"
     class="ui-widget-content ui-state-default ui-corner-all ui-widget">
    <?php echo __('Next') ?>
  </a>
</p>

<script type="text/javascript">
$(document).ready(function(){
  document.seated_plan_functions.push(function()
  {
    $('.seated-plan .seat.txt').click(function(){
      if ( $('#todo .ticket').length == 0 )
        return false;
      
      alert('send a GET request to associate a ticket on a place');
      {
        var id = $(this).clone(true).removeClass('seat').removeClass('txt').attr('class');
        $('#todo .ticket:first').prependTo('#done');
        $('.seated-plan .'+id).addClass('ordered')
        $(this).addClass('in-progress');
      }
      
      // if there is no more ticket, go to the next step, including editting the order
      if ( $('#todo .ticket').length == 0 )
        window.location = $('.next a').prop('href');
    });
  });
});
</script>

</div>
</div>
