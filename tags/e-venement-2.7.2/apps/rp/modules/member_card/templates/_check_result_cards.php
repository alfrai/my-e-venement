<table>
<tbody>
  <?php use_helper('Date') ?>
  <?php foreach ( $member_cards as $mc ): ?>
  <?php if ( $mc->id != $member_card->id ): ?>
  <tr class="other-card">
    <td>#<?php echo link_to($mc->id,'member_card/show?id='.$mc->id) ?></td>
    <td><?php echo format_date($mc->expire_at) ?></td>
    <td><?php echo image_tag( strtotime($mc->expire_at) > strtotime('now') ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png') ?></td>
  </tr>
  <?php endif ?>
  <?php endforeach ?>
</tbody>
<thead>
  <tr>
    <td colspan="3"><?php echo __('Other cards') ?></td>
  </tr>
</thead>
</table>
