<?php use_helper('Date') ?>
<?php $sf_user->setFlash('notice',$sf_user->getFlash('notice')) ?>
<?php include_partial('contact/assets') ?>
<script type="text/javascript">
$(document).ready(function(){
  window.print();
  <?php if ( sfConfig::get('app_cards_auto_close', true) ): ?>
  <?php if ( is_null($transaction) ): ?>
  window.close();
  <?php else: ?>
  window.location = '<?php echo cross_app_url_for('tck',($card->BoughtProducts->count() > 0 ? 'transaction/edit' : 'ticket/pay').'?id='.$transaction->id) ?>';
  <?php endif ?>
  <?php elseif ( !is_null($transaction) ): ?>
  window.open('<?php echo cross_app_url_for('tck','ticket/pay?id='.$transaction->id) ?>');
  <?php endif ?>
});
</script>
<div id="card">
  <div class="content">
    <p class="picture"></p>
    <p class="name"><span class="title"><?php echo __('Name') ?></span> <?php echo $contact->name ?></p>
    <p class="firstname"><span class="title"><?php echo __('Firstname') ?></span> <?php echo $contact->firstname ?></p>
    <p class="barcode"><?php echo image_tag('/liBarcodePlugin/php-barcode/barcode.php?scale=1&code='.$contact->getIdBarcoded()); ?></p>
    <p class="extra-card"><?php echo nl2br(sfConfig::get('app_cards_extra')) ?></p>
  </div>
  <div class="content">
    <p class="name"><span class="title"><?php echo __('Name') ?></span><br/><?php echo $contact->name ?></p>
    <p class="firstname"><span class="title"><?php echo __('Firstname') ?></span><br/><?php echo $contact->firstname ?></p>
    <p class="address"><span class="title"><?php echo __('Address') ?></span><br/><?php echo nl2br(trim($contact->address)) ?></p>
    <p class="city"><?php echo $contact->postalcode.' '.$contact->city ?></p>
    <p class="country"><?php echo $contact->country ?></p>
    <p class="status"><span class="title"><?php echo __('Status') ?></span> <?php echo nl2br(__($card->name)) ?></p>
    <p class="date"><span class="title"><?php echo __('Expiration') ?></span> <?php echo format_date($card->expire_at) ?></p>
    <p class="barcode"><?php echo image_tag('/liBarcodePlugin/php-barcode/barcode.php?scale=1&code='.$contact->getIdBarcoded()); ?></p>
  </div>
  <div class="content">
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
