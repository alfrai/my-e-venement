    <td class="event"><?php echo __('Event') ?></td>
    <td class="see-more"></td>
    <td class="id-qty"><?php echo __('Qty') ?></td>
    <td class="value"><?php echo __('PIT') ?></td>
    <td class="value"><?php echo __('Taxes', null, 'li_accounting') ?></td>
    <?php foreach ( $total['vat'] as $name => $arr ): ?>
    <td class="vat"><?php echo format_number(round($name*100,2)).'%'; ?></td>
    <?php endforeach ?>
    <td class="vat total"><?php echo __('Tot.VAT') ?></td>
    <td class="tep"><?php echo __('TEP') ?></td>
