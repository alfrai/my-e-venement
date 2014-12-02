<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php $module_name = $sf_context->getModuleName() ?>
    <?php $sf_response->setTitle('e-venement, '.__(strtoupper(substr($module_name,0,1)).substr($module_name,1))) ?>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="/favicon.ico" />
    <script src="<?php echo url_for('contact') ?>/../../js/jquery.js" type="text/javascript"></script>
    <script src="<?php echo url_for('option_labels/js') ?>" type="text/javascript"></script>
    <link rel="stylesheet" media="all" type="text/css" href="<?php echo url_for('option_labels/css') ?>" />
  </head>
  <body class="labels">
    <?php include_partial('contact/labels',array('labels' => $labels, 'params' => $params, 'fields' => $fields, )) ?>
  </body>
</html>
