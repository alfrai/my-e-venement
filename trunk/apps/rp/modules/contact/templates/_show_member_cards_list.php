<?php if ( !isset($contact) ) $contact = $form->getObject() ?>
<?php if ( sfConfig::has('app_cards_enable') ): ?>
<?php if ( $contact->MemberCards->count() > 0 ): ?>
<div class="sf_admin_form_row">
  <label><?php echo link_to(__('Member cards'),'contact/card?id='.$contact->id) ?>:</label>
  <ul class="show_member_cards_list">
  <?php foreach ( $contact->MemberCards as $card ): ?>
  <?php if ( strtotime($card->expire_at) > strtotime('now') ): ?>
    <li><?php echo $card ?></li>
  <?php endif ?>
  <?php endforeach ?>
  </ul>
</div>
<?php endif ?>
<?php endif ?>
