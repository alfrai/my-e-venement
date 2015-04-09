<?php $json = $sf_data->getRaw('json') ?>
<?php if ( !is_array($json) ) $json = array() ?>
<?php $json['success']['message'] = __('Congratulations, your tickets are now seated.') ?>
<?php echo json_encode($json) ?>
