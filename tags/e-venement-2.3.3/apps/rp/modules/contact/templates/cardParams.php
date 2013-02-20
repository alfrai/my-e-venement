<?php include_partial('contact/assets') ?>
<?php include_partial('global/flashes') ?>
<?php echo $card->renderFormTag(url_for('contact/card'),array('class' => 'ui-widget-content ui-corner-all', 'id' => 'member-cards', 'target' => '_blank')) ?>
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __("%%c%%'s member cards",array('%%c%%' => $contact)) ?></h1>
  </div>
  <div class="sf_admin_actions_block ui-widget">
    <?php include_partial('card_actions',array('contact' => $contact,'helper' => $helper)) ?>
  </div>
  <div style="clear: both"></div>
  <div class="ui-widget-content ui-corner-all new">
    <?php include_partial('card_new',array('form' => $form, 'member_card_types' => $member_card_types, 'card' => $card, 'payment_methods' => $payment_methods)) ?>
  </div>
  <div class="ui-widget-content ui-corner-all list">
    <?php include_partial('card_list',array('contact' => $contact,)) ?>
  </div>
</form>
