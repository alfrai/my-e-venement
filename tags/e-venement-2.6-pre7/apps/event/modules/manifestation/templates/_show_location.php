<div class="sf_admin_form_row sf_admin_form_field_location">
  <label><?php echo __('Location') ?>:</label>
  <?php if ( $manifestation->current_version ): ?>
  <span class="diff">
    <?php echo link_to($loc = Doctrine::getTable('Location')->findOneById($manifestation->location_id), 'location/show?id='.$loc->id); ?>
  </span>
  <?php endif ?>
  <?php echo link_to($manifestation->Location,'location/show?id='.$manifestation->Location->id) ?>
</div>
