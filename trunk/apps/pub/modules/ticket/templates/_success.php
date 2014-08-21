<?php $json = $sf_data->getRaw('json') ?>
<?php if ( !is_array($json) ) $json['success'] = array() ?>
<?php $json['success']['message'] = isset($json['success']['message']) ? __($json['success']['message']) : __('Action successful') ?>
<?php echo json_encode($json) ?>
