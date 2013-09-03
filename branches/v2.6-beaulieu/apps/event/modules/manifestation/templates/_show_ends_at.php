<div class="sf_admin_form_row sf_admin_form_field_ends_at">
<label><?php echo __('Ends at') ?>:</label>
<?php echo format_date(strtotime($manifestation->happens_at)+$manifestation->duration,'dddd dd MMMM yyyy HH:mm') ?>
</div>
