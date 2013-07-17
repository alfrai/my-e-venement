<?php if ( isset($manifestation) && $manifestation instanceof Manifestation ): ?>
<div class="sf_admin_form_row is_necessary_to">
  <label><?php echo __('Necessary to') ?>:</label>
  <ul>
  <?php foreach ( $manifestation->IsNecessaryTo as $manif ): ?>
    <li><?php echo link_to($manif, 'manifestation/show?id='.$manif->id) ?></li>
  <?php endforeach ?>
  </ul>
</div>
<?php endif ?>
