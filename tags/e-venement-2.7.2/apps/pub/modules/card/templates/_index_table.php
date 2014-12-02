<table id="member_card_types">
  <tbody>
  <?php foreach ( $member_card_types as $type ): ?>
    <tr>
      <td class="name"><?php echo $type->description ? $type->description : $type ?></td>
      <td class="value"><?php echo format_currency($type->value,'€') ?></td>
      <td class="qty">x <input type="qty" name="member_card_type[<?php echo $type->id ?>]" value="<?php echo isset($mct[$type->id]) ? $mct[$type->id] : 0 ?>" maxlength="2" /></td>
      <td class="operand">=</td>
      <td class="total"><?php echo format_currency(0,'€') ?></td>
    </tr>
  <?php endforeach ?>
  </tbody>
  <tfoot>
    <tr>
      <td class="name"></td>
      <td class="value"></td>
      <td class="qty">0</td>
      <td class="operand">=</td>
      <td class="total"><?php echo format_currency(0,'€') ?></td>
    </tr>
  </tfoot>
</table>
