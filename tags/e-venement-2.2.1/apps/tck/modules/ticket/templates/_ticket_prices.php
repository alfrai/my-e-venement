<?php use_helper('Number') ?>
<div class="gauge ui-widget-content ui-corner-all"></div>
<form action="<?php echo url_for('ticket/ticket') ?>" method="post" class="tickets_form">
<div><?php echo link_to('command','ticket/ticket?id='.$transaction->id) ?></div>
  <p class="title"><?php echo __('Prices') ?>:</p>
  <p class="prices_list ui-corner-all">
    <input name="ticket[nb]" value="1" type="text" size="4" maxlength="3" />
    <!--
    <select name="ticket[nb]">
      <option value="-1">-1</option>
      <option value="1" selected="selected">+1</option>
      <option value="2">+2</option>
      <option value="3">+3</option>
      <option value="4">+4</option>
      <option value="5">+5</option>
      <option value="6">+6</option>
      <option value="7">+7</option>
      <option value="8">+8</option>
      <option value="9">+9</option>
      <option value="10">+10</option>
    </select>
    -->
    <input autocomplete="off" type="checkbox" name="select_all" value="true" title="<?php echo __('Add to all manifestations') ?>" />
  <?php foreach ( $prices as $price ): ?>
    <input type="submit" name="ticket[price_name]" value="<?php echo $price ?>" title="<?php echo $price->description.' (def: '.format_number(round($price->value,2)).'€)' ?>" />
  <?php endforeach ?>
  <a href="<?php echo url_for('ticket/cancelPartial') ?>"
     onclick="javascript: $(this).unbind(); $(this).closest('.prices_list').toggleClass('cancel'); return false;"
     class="ui-icon cancel"
     title="<?php echo __('Cancel printed tickets as you click on prices now.') ?>">
     <?php echo __('Cancel printed tickets as you click on prices now.') ?>
  </a>
  </p>
</form>
