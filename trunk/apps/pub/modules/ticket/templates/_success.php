<?php $json = $sf_data->getRaw('json') ?>
<?php if ( !is_array($json) ) $json['success'] = array() ?>
<?php $json['success']['message'] = isset($json['success']['message']) ? $json['success']['message'] : 'Action successful' ?>
<?php if (!( isset($no_i18n) && $no_i18n )) $json['success']['message'] = __($json['success']['message']) ?>
<?php echo sfConfig::get('sf_web_debug',false) ? '<pre>'.print_r($json,true).'</pre>' : json_encode($json) ?>
