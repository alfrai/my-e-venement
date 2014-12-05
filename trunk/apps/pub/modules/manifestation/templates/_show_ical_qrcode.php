<?php if ( sfConfig::get('app_options_ical_qrcode', false) ): ?>
<div class="manifestation-ical" title="<?php echo __('Flash me to complete your calendar...') ?>">
  <?php $qrcode = new liBarcode($manifestation->getRawValue()->getIcal(false)) ?>
  <img src="data:image/png;base64,<?php echo base64_encode($qrcode->render(NULL, 4, QR_ECLEVEL_S)) ?>" alt="iCal" />
</div>
<?php endif ?>
