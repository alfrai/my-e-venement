<div class="sf_admin_form_row depends_on">
  <label><?php echo __('Depends on') ?>:</label>
  <?php if ( $manifestation->depends_on ) echo link_to($manifestation->DependsOn, 'manifestation/show?id='.$manifestation->depends_on) ?>
</div>
