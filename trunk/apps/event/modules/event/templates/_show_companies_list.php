<div class="sf_admin_form_row sf_admin_field_companies_list">
  <label><?php echo __('Companies list') ?>:</label>
  <ul class="ui-corner-all ui-widget-content">
    <?php if ( $event->Companies->count() == 0 ): ?>
      <li><?php echo __('No registered company') ?></li>
    <?php else: ?>
    <?php foreach ( $event->Companies as $company ): ?>
    <li><a href="<?php echo url_for('organism/show?id='.$company->id) ?>"><?php echo $company ?></a></li>
    <?php endforeach ?>
    <?php endif ?>
  </ul>
</div>
