<div class="sf_admin_form_row sf_admin_form_field_event">
  <label><?php echo __('Event') ?>:</label>
  <?php if ( $manifestation->current_version ): ?>
  <span class="diff">
    <?php echo link_to($event = Doctrine::getTable('Event')->findOneById($manifestation->event_id), 'event/show?id='.$event->id) ?>
  </span>
  <?php endif ?>
  <?php echo link_to($manifestation->Event,'event/show?id='.$manifestation->Event->id) ?>
</div>
