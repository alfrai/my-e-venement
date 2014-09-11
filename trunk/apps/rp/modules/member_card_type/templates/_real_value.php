<?php
  $val = 0;
  foreach ( $member_card_type->ProductDeclination->Product->PriceProducts as $pp )
  if ( $pp->price_id == $member_card_type->price_id )
    $val = $pp->value;
?>
<?php echo format_currency($member_card_type->value - $val,'€') ?>
