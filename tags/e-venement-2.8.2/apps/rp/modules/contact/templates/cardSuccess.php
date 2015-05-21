<?php use_helper('Date') ?>
<?php $sf_user->setFlash('notice',$sf_user->getFlash('notice')) ?>
<?php include_partial('contact/assets') ?>
<script type="text/javascript">
$(document).ready(function(){
  window.print();
  <?php
    $nb = 0;
    foreach ( $transaction->MemberCards as $mc )
    $nb += $mc->BoughtProducts->count();
  ?>
  <?php if ( sfConfig::get('app_cards_auto_close', true) ): ?>
    <?php if ( $transaction->Payments->count() > 0 || $nb > 0 ): ?>
      window.location = '<?php echo cross_app_url_for('tck',($nb > 0 ? 'transaction/edit' : 'ticket/pay').'?id='.$transaction->id) ?>';
    <?php else: ?>
      window.close();
    <?php endif ?>
  <?php elseif ( $transaction->Payments->count() > 0 || $nb > 0 ): ?>
    window.open('<?php echo cross_app_url_for('tck','ticket/pay?id='.$transaction->id) ?>');
  <?php endif ?>
});
</script>
<?php foreach ( $transaction->MemberCards as $card ): ?>
<div class="page">
<div class="member_card">
  <div class="content card">
    <p class="picture"></p>
    <p class="name"><span class="title"><?php echo __('Name') ?></span> <?php echo $contact->name ?></p>
    <p class="firstname"><span class="title"><?php echo __('Firstname') ?></span> <?php echo $contact->firstname ?></p>
    <p class="barcode"><?php echo image_tag('/liBarcodePlugin/php-barcode/barcode.php?scale=1&code='.$contact->getIdBarcoded()); ?></p>
    <p class="extra-card"><?php echo nl2br(sfConfig::get('app_cards_extra')) ?></p>
  </div>
  <div class="content archive">
    <p class="name"><span class="title"><?php echo __('Name') ?></span><br/><?php echo $contact->name ?></p>
    <p class="firstname"><span class="title"><?php echo __('Firstname') ?></span><br/><?php echo $contact->firstname ?></p>
    <p class="address"><span class="title"><?php echo __('Address') ?></span><br/><?php echo nl2br(trim($contact->address)) ?></p>
    <p class="city"><?php echo $contact->postalcode.' '.$contact->city ?></p>
    <p class="country"><?php echo $contact->country ?></p>
    <p class="status"><span class="title"><?php echo __('Status') ?></span> <?php echo nl2br(__($card->name)) ?></p>
    <p class="date"><span class="title"><?php echo __('Expiration') ?></span> <?php echo format_date($card->expire_at) ?></p>
    <p class="barcode"><?php echo image_tag('/liBarcodePlugin/php-barcode/barcode.php?scale=1&code='.$contact->getIdBarcoded()); ?></p>
  </div>
  <div class="content receipt">
    <p class="librinfo">Imprimé et géré par e-venement www.libre-informatique.fr</p>
    <h2><?php echo __('Card receipt') ?></h2>
    <p class="name"><span class="title"><?php echo __('Name') ?></span><br/><?php echo $contact->name ?></p>
    <p class="firstname"><span class="title"><?php echo __('Firstname') ?></span><br/><?php echo $contact->firstname ?></p>
    <p class="address"><span class="title"><?php echo __('Address') ?></span><br/><?php echo nl2br(trim($contact->address)) ?></p>
    <p class="city"><?php echo $contact->postalcode.' '.$contact->city ?></p>
    <p class="country"><?php echo $contact->country ?></p>
    <p class="status"><span class="title"><?php echo __('Status') ?></span> <?php echo nl2br(__($card->name)) ?></p>
    <p class="date"><span class="title"><?php echo __('Expiration date') ?></span> <?php echo format_date($card->expire_at) ?></p>
    <p class="extra-date"><?php echo nl2br(sfConfig::get('app_cards_date_extra')) ?></p>
    <p class="extra-card"><?php echo nl2br(sfConfig::get('app_cards_extra')) ?></p>
  </div>
</div>
</div>
<div class="mc_separator"></div>
<?php endforeach ?>
