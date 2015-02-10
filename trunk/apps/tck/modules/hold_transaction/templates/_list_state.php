<?php $var = 'module_ht_quantity' ?>
<?php $nb = $hold_transaction->pretickets > $hold_transaction->Transaction->Tickets->count() ? $hold_transaction->pretickets : $hold_transaction->Transaction->Tickets->count() ?>
<?php if ( sfConfig::get($var, false) === false ) sfConfig::set($var, $hold_transaction->Hold->Seats->count()) ?>
<span class="<?php
  if ( sfConfig::get($var) >= $nb )
  {
    echo 'li-hold-ok';
    sfConfig::set($var, sfConfig::get($var) - $nb);
  }
  elseif ( sfConfig::get($var) > 0 )
    echo 'li-direct-out-of-hold';
  else
    echo 'li-out-of-hold';
?>"></span>
