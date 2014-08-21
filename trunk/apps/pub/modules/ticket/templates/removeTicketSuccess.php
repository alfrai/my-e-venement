<?php $json = $sf_data->getRaw('json') ?>
<?php if ( !is_array($json) ) $json = array() ?>
<?php $json['success']['message'] = __('The seat has been freed with success') ?>
<?php echo json_encode($json) ?>
