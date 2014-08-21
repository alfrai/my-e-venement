<?php $json = $sf_data->getRaw('json') ?>
<?php if ( !is_array($json) ) $json['success'] = array() ?>
<?php $json['success']['message'] = __('Ticket pre-seated with success') ?>
<?php echo json_encode($json) ?>
