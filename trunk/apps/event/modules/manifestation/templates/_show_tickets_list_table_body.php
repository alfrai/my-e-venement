  <tr>
    <?php echo get_class(array_pop($contact)) ?>
    <td class="name"><?php echo $price ?></td>
    <td class="qty"><?php echo count($transaction) ?></td>
    <td class="transaction"><?php echo implode(', ',$transaction) ?></td>
    <td class="contact"><?php echo implode(', ',array_merge(array_values($pro),array_values($contact))) ?></td>
  </tr>
