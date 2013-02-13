<td><?php echo __('Member card') ?></td>
<td>
  <?php if ( $ticket->MemberCard ): ?>
  <a href="<?php echo cross_app_url_for('rp','contact/show?id='.$ticket->MemberCard->contact_id) ?>">
    <?php echo $ticket->MemberCard->Contact ?>
  </a>
  <?php endif ?>
</td>
<td>
  <?php if ( $ticket->MemberCard ): ?>
  <a href="<?php echo cross_app_url_for('rp','member_card/show?id='.$ticket->MemberCard->id) ?>">
    #<?php echo $ticket->MemberCard->id ?>
  </a>
  <?php endif ?>
</td>
<td></td>

