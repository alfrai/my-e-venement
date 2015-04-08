<?php use_helper('Date') ?>
<p class="">
<a href="<?php echo cross_app_url_for('event','manifestation/show?id='.$manifestation_entry->Manifestation->id) ?>">
<?php echo format_date($manifestation_entry->Manifestation->happens_at,'EEE, dd MMM yyyy') ?>
</a>
 @
<a href="<?php echo cross_app_url_for('event','location/show?id='.$manifestation_entry->Manifestation->Location->id) ?>">
  <?php echo $manifestation_entry->Manifestation->Location ?>
</a>
</p>
