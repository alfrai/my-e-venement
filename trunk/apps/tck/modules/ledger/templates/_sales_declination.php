<td class="event"><?php echo str_replace('__', '', $declination['id']) === ''.$declination['id']
  ? cross_app_link_to($declination['name'], 'pos', 'product/show?id='.$pdt['id'].'#sf_fieldset_declinations') // go a find the id in the parent element
  : $declination['name']
?></td>
<td class="see-more"><a href="#declination-<?php echo slugify($dname) ?>">-</a></td>
<td class="id-qty"><?php echo $declination['qty'] ?></td>
<td class="value"><?php echo format_currency($declination['value'], '€') ?></td>
<td class="extra-taxes"></td>
<?php $local_vat = 0 ?>
<?php foreach ( $vat as $t ): if ( $t[$pdtname][$dname]['__total__'] ): ?>
  <?php $local_vat += round($t[$pdtname][$dname]['__total__'],2) ?>
  <td class="vat"><?php echo format_currency(round($t[$pdtname][$dname]['__total__'],2),'€') ?></td>
<?php else: ?>
<td class="vat"></td>
<?php endif; endforeach ?>
<td class="vat total"><?php echo format_currency($local_vat,'€') ?></td>
<td class="tep"><?php echo format_currency($declination['value'] - $local_vat,'€') ?></td>
