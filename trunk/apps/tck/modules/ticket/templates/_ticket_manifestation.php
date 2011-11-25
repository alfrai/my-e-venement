<?php use_helper('Date','Number') ?>
<span class="manif" style="background-color: #<?php echo $manif->Color ? $manif->Color->color : '' ?>; padding: 5px;">
  <input type="radio" name="ticket[manifestation_id]" value="<?php echo $manif->id ?>" <?php if ( isset($first) && $first ) echo 'checked="checked"' ?>  />
  <a href="<?php echo cross_app_url_for('event','event/show?id='.$manif->event_id) ?>"><?php echo $manif->Event ?></a>
  le <a href="<?php echo cross_app_url_for('event','manifestation/show?id='.$manif->id) ?>"><?php echo format_datetime($manif->happens_at,'d MMM yyyy HH:mm') ?></a>
</span>
<span class="prices">
<?php if ( $active ): ?>
  <?php $total = 0 ?>
  <?php foreach ( $manif->Tickets as $ticket ): ?>
    <?php if ( is_null($ticket->duplicate) ): ?>
    <input alt="#<?php echo $ticket->id ?>" type="hidden" name="ticket[prices][<?php echo $manif->id ?>][<?php echo $ticket->Price ?>][]" value="<?php echo $ticket->value ?>" title="PU: <?php echo format_currency($ticket->value,'€') ?>" />
    <?php $total += $ticket->value ?>
    <?php endif ?>
  <?php endforeach ?>
<?php endif ?>
</span>
<span class="total"><?php if ( $active ) echo format_currency($total,'€') ?></span>
