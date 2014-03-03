<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php $module_name = $sf_context->getModuleName() ?>
    <?php $sf_response->setTitle(sfConfig::get('app_title')) ?>
    <?php use_javascript('public') ?>
    <?php use_javascript('/private/public.js') ?>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="<?php echo image_path('logo-evenement.png') ?>" />
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
  </head>
  <body>
    <div id="content">
      <?php include_partial('global/oplog') ?>
      <?php echo $sf_content ?>
    </div>
    <ul id="menu" class="first">
      <?php include_partial('global/public_choices') ?>
    </ul>
    <div id="footer">
      <?php include_partial('global/footer') ?>
      <?php include_partial('global/date') ?>
      <?php include_partial('global/cart_widget') ?>
    </div>
    <div id="transition"><span class="close"></span></div>
  </body>
</html>
