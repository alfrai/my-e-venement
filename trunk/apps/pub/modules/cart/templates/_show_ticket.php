  <td class="tickets">
    <span class="price-<?php echo $ticket->price_id ?>">
      <?php echo $ticket->Price->description ?>
    </span>
  </td>
  <td class="qty"></td>
  <td class="value">
    <?php use_helper('Number') ?>
    <?php echo format_currency($ticket->value,'€') ?>
  </td>
  <td class="total">
    <?php use_helper('Number') ?>
    <?php echo format_currency($ticket->value,'€') ?>
  </td>
