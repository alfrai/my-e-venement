<div class="sf_admin_form_row sf_admin_form_field_event_category">
  <label><?php echo __('Category') ?>:</label>
  <?php if ( $event->EventCategory ): ?>
  <?php echo link_to($event->EventCategory,'event_category/show?id='.$event->EventCategory->id) ?>
  <?php endif ?>
</div>
