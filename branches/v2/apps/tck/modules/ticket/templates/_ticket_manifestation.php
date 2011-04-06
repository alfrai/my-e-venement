<?php use_helper('Date','Number') ?>
<span class="manif">
  <input type="radio" name="ticket[manifestation_id]" value="<?php echo $manif->id ?>" <?php if ( isset($first) && $first ) echo 'checked="checked"' ?>  />
  <a href="<?php echo cross_app_url_for('event','event/show?id='.$manif->event_id) ?>"><?php echo $manif->Event ?></a>
  le <a href="<?php echo cross_app_url_for('event','manifestation/show?id='.$manif->id) ?>"><?php echo format_datetime($manif->happens_at) ?></a>
</span>
<span class="prices">
  <?php $total = 0 ?>
  <?php foreach ( $manif->Tickets as $ticket ): ?>
    <input type="hidden" name="ticket[prices][<?php echo $manif->id ?>][<?php echo $ticket->Price ?>][]" value="<?php echo $ticket->value ?>" title="PU: <?php echo format_currency($ticket->value,'€') ?>" />
    <?php $total += $ticket->value ?>
  <?php endforeach ?>
</span>
<span class="total"><?php echo format_currency($total,'€') ?></span>
