<?php use_helper('Number') ?>
<tfoot>
  <tr>
    <td class="name">Total</td>
    <td class="qty"><?php echo $total['qty'] ?></td>
    <td class="price"><?php echo format_currency($total['value'],'€') ?></td>
    <td class="transaction">-</td>
    <td class="contact">-</td>
  </tr>
</tfoot>
