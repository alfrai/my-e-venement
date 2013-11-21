<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php use_javascript('/liFancyboxPlugin/jquery.fancybox-1.3.4.pack.js') ?>
    <?php use_stylesheet('/liFancyboxPlugin/jquery.fancybox-1.3.4.css') ?>
    <?php use_stylesheet('/private/main.css') ?>
    
    <?php $module_name = $sf_context->getModuleName() ?>
    <?php $sf_response->setTitle('e-venement, '.__(strtoupper(substr($module_name,0,1)).substr($module_name,1))) ?>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="<?php echo image_path('logo-evenement.png') ?>" />
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
  </head>
  <body class="nude">
      <?php echo $sf_content ?>
  </body>
</html>
