<div class="sf_admin_form_row">
  <label><?php echo __('Region') ?>:</label>
  <?php
    if ( (trim(strtolower($contact->country)) === 'france' || trim($contact->country) === '') && $contact->postalcode )
    {
      $dpt = Doctrine::getTable('GeoFrDepartment')->fetchOneByNumCP(substr($contact->postalcode,0,2));
      echo $dpt ? $dpt->Region : '&nbsp;';
    }
  ?>
</div>
