<table>
<tbody>
  <tr class="contact">
    <td colspan="2"><a href="<?php echo url_for('contact/show?id='.$member_card->Contact->id) ?>">
      <?php echo $member_card->Contact ?>
    </a></td>
  </tr>
  <tr class="card_id">
    <td><?php echo __('Id') ?></td>
    <td>#<?php echo link_to($member_card->id,'member_card/show?id='.$member_card->id) ?></td>
  </tr>
  <tr class="card_expiration">
    <?php use_helper('Date') ?>
    <td><?php echo __('Expire at') ?></td>
    <td><?php echo format_date($member_card->expire_at) ?></td>
  </tr>
  <?php if ( $member_card->Payments->count() > 0 ): ?>
  <tr class="value">
    <?php use_helper('Number') ?>
    <td><?php echo __('Value') ?></td>
    <td><?php echo format_currency($member_card->value,'€') ?></td>
  </tr>
  <?php endif ?>
  <?php if ( $member_card->nb_prices > 0 ): ?>
  <tr class="prices">
    <td><?php echo __('Associated prices still available') ?></td>
    <td><?php echo $member_card->nb_prices ?></td>
  </tr>
  <?php endif ?>
</tbody>
</table>
