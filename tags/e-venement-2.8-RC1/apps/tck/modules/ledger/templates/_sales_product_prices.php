  <td class="event price"><?php echo __('%%price%% (by %%user%%)',array('%%price%%' => $price['name'], '%%user%%' => $price['user'])) ?></td>
  <td class="see-more"></td>
  <td class="id-qty"><?php echo $price['qty'] ?></td>
  <td class="value"><?php echo format_currency($price['value'],'€') ?></td>
  <td class="extra-taxes"><?php echo format_currency($price['taxes'],'€') ?></td>
  <?php foreach ( $total['vat'] as $t => $v ): ?>
    <td class="vat"></td>
  <?php endforeach ?>
  <td class="vat total"></td>
  <td class="tep"></td>
