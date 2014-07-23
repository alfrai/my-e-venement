<?php use_helper('Date') ?>
<span class="tdp-dates sf_admin_form_field_dates">
<span title="<?php echo __('Created at') ?>"><?php echo format_datetime($organism->created_at) ?></span>
<br/>
<span title="<?php echo __('Updated at') ?>"><?php echo format_datetime($organism->updated_at) ?></span>
</span>
