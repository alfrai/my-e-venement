<div id="card">
  <div class="content">
    <p class="name"><?php echo $contact->title.' '.$contact ?></p>
    <p class="address"><?php echo nl2br($contact->address) ?></p>
    <p class="city"><?php echo $contact->postalcode.' '.$contact->city ?></p>
    <p class="country"><?php echo $contact->country ?></p>
    <p class="barcode"><?php echo image_tag('/liBarcodePlugin/php-barcode/barcode.php?scale=1&code='.$contact->getIdBarcoded()); ?></p>
  </div>
</div>
