<td class="event"><?php echo cross_app_link_to(format_date($manif->happens_at).' @ '.$manif->Location,'event','manifestation/show?id='.$manif->id) ?></td>
<td class="see-more"><a href="#manif-<?php echo $manif->id ?>">-</a></td>
<td class="id-qty">
  <?php if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) ): ?>
  <?php $nb = $manif->Tickets->count(); foreach ( $manif->Tickets as $t ) if ( !is_null($t->cancelling) ) $nb-=2; echo $nb; ?>
  <?php else: ?>
  <?php echo $infos[$manif->id]['qty']; $total['qty'] += $infos[$manif->id]['qty']; ?>
  <?php endif ?>
</td>
<td class="value" title="<?php echo __('PIT').' = '.__('TEP').' + '.__('Tot.VAT') ?>">
  <?php if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) ): ?>
  <?php $value = 0; foreach ( $manif->Tickets as $ticket ) $value += $ticket->value; echo format_currency($value,'€'); ?>
  <?php else: ?>
  <?php echo format_currency($value = $infos[$manif->id]['value'],'€'); ?>
  <?php endif ?>
</td>
<td class="extra-taxes" title="<?php echo __('Incl. VAT') ?>">
  <?php if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) ): ?>
  <?php $taxes = 0; foreach ( $manif->Tickets as $ticket ) $taxes += $ticket->taxes; echo format_currency($taxes,'€'); ?>
  <?php else: ?>
  <?php echo format_currency($taxes = $infos[$manif->id]['taxes'],'€'); ?>
  <?php endif ?>
</td>
<?php foreach ( $vat as $t ): if ( isset($t[$event->id][$manif->id]) ): ?>
<td class="vat"><?php $local_vat += round($t[$event->id][$manif->id],2); echo format_currency(round($t[$event->id][$manif->id],2),'€') ?></td>
<?php else: ?>
<td class="vat"></td>
<?php endif; endforeach ?>
<td class="vat total"><?php echo format_currency($local_vat,'€') ?></td>
<td class="tep"><?php echo format_currency($value - $local_vat,'€') ?></td>
