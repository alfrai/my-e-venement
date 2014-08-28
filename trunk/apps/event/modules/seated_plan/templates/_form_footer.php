<div class="js_seated_plan_useful">
  <span class="prompt_seat_name"><?php echo __("Seat's name") ?></span>
  <span class="alert_seat_duplicate"><?php echo __("This seat's name has already been given.") ?></span>
  <span class="save_error"><?php echo __("An error occurred during the plot recording. Try again.") ?></span>
  <form class="seat_add" action="<?php echo url_for('seated_plan/seatAdd?id='.$form->getObject()->id) ?>" method="get"><p>
    <input type="text" name="seat[name]" value="" />
    <input type="text" name="seat[x]" value="" />
    <input type="text" name="seat[y]" value="" />
    <input type="text" name="seat[diameter]" value="" />
  </p></form>
  <form class="seat_del" action="<?php echo url_for('seated_plan/seatDel?id='.$form->getObject()->id) ?>" method="get"><p>
    <input type="text" name="seat[id]" value="" />
  </p></form>
</div>
