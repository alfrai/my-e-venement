<?php use_helper('Number') ?>
<div class="gauge ui-widget-content ui-corner-all"></div>
<form action="<?php echo url_for('ticket/ticket') ?>" method="post" class="tickets_form">
<?php if ( !(isset($remove_manifestations_list) && $remove_manifestations_list) ): ?>
<div><?php echo link_to('command','ticket/ticket?id='.$transaction->id) ?></div>
<?php endif ?>
  <p class="title"><?php echo __('Prices') ?>:</p>
  <p class="prices_list ui-corner-all">
    <input name="ticket[nb]" value="1" type="text" size="4" maxlength="3" autocomplete="off" />
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
