<div class="sf_admin_form_row">
  <label><?php echo __('Region') ?>:</label>
  <?php
    if ( (trim(strtolower($organism->country)) === 'france' || trim($organism->country) === '') && $organism->postalcode )
    {
      $dpt = Doctrine::getTable('GeoFrDepartment')->fetchOneByNumCP(substr($organism->postalcode,0,2));
      echo $dpt ? $dpt->Region : '&nbsp;';
    }
  ?>
</div>
