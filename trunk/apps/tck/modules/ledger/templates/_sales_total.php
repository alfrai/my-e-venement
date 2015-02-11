    <?php $local_vat = 0; ?>
    <td class="event"><?php echo __('Total') ?> <span class="super-total"><?php echo format_currency($total['value']+$total['taxes'],'€') ?></span></td>
    <td class="see-more"></td>
    <td class="id-qty"><?php echo $total['qty'] ?></td>
    <td class="value"><?php echo format_currency($total['value'],'€'); ?></td>
    <td class="extra-taxes"><?php echo format_currency($total['taxes'],'€'); ?></td>
    <?php foreach ( $total['vat'] as $v ): ?>
    <td class="vat"><?php echo format_currency(round($v,2),'€'); $local_vat += round($v,2); ?></td>
    <?php endforeach ?>
    <td class="vat total"><?php echo format_currency($local_vat,'€') ?></td>
    <td class="tep"><?php echo format_currency(round($total['value'],2)-$local_vat,'€'); ?></td>
