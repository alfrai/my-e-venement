  <td class="event price"><?php echo __('%%price%% (by %%user%%)',array('%%price%%' => $price['name'], '%%user%%' => $price['user'])) ?></td>
  <td class="see-more"></td>
  <td class="id-qty"><?php echo $price['qty'] ?></td>
  <td class="value"><?php echo format_currency($price['value'],'€') ?></td>
  <td class="extra-taxes"></td>
  <?php foreach ( $total['vat'] as $t => $v ): ?>
    <td class="vat"></td>
  <?php endforeach ?>
  <td class="vat total"></td>
  <td class="tep"></td>
  <?php /* ?>
  <?php $local_vat = 0 ?>
  <?php foreach ( $total['vat'] as $t => $v ): ?>
    <?php $local_vat += round($t[$pdtname][$dname][$prname]['__total__'],2) ?>
    <td class="vat"><?php echo format_currency(round($t[$pdtname][$dname][$prname]['__total__'],2),'€') ?></td>
  <?php endforeach ?>
  <td class="vat total"><?php echo format_currency($local_vat, '€') ?></td>
  <td class="tep"><?php echo format_currency($price['value'] - $local_vat, '€') ?></td>
  <?php */ ?>
