<div class="sf_admin_form_row sf_admin_integer sf_admin_form_field_show_nb_seats">
  <div class="label ui-helper-clearfix">
    <label for="show_nb_seats"><?php echo __('Nb of seats').':' ?></label>
    <div class="help">
      <span class="ui-icon ui-icon-help floatleft"></span>
      <?php echo __('This is the number of seats when the screen loaded.') ?>
    </div>
  </div>
  <span class="nb"><?php echo $form->getObject()->Seats->count() ?></span>
</div>
