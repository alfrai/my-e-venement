<?php use_javascript('rp-member-card-seat') ?>
<div class="sf_admin_form_row">
  <label><?php echo __('Privileged seat') ?>:</label>
  <span class="seat_name"><?php echo $member_card->privileged_seat_name ?></span>
  <img src="/sfDoctrinePlugin/images/tick.png" style="display: none">
  <a
    href="<?php echo url_for('member_card/setSeat?id='.$member_card->id) ?>"
    class="fg-button-mini fg-button ui-state-default fg-button-icon-left"
    style="font-size: 0px; padding: 8px; float: none;"
    title="<?php echo __('Change seat') ?>"
    target="_blank"
    onclick="javascript: LI.memberCardChangeSeat(this); return false;"
  >
    <span class="ui-icon ui-icon-radio-off"></span>
    <?php echo __('Change seat') ?>
  </a>
  <div class="label ui-helper-clearfix"><?php echo __('Case and space sensitive') ?></div>
</div>
