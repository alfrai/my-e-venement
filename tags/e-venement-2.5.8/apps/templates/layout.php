<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php use_javascript('menu') ?>
    <?php use_stylesheet('menu') ?>
    <?php use_stylesheet('/private/main.css') ?>
    <?php use_stylesheet('about') ?>
    <?php use_javascript('jquery.datepicker-fr.js') ?>
    
    <?php $module_name = $sf_context->getModuleName() ?>
    <?php $sf_response->setTitle('e-venement, '.($sf_user->isAuthenticated() ? __(strtoupper(substr($module_name,0,1)).substr($module_name,1)) : __('The free ticketting system',null,'menu'))) ?>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="<?php echo image_path('logo-evenement.png') ?>" />
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
  </head>
  <body>
    <div id="content">
      <?php echo $sf_content ?>
    </div>
    <ul id="menu" class="first">
      <?php include_partial('global/menu') ?>
    </ul>
    <div id="banner">
      <a href="<?php echo cross_app_url_for('default','sf_guard_signout') ?>" onclick="javascript: window.close()"><?php echo image_tag("close.png",array('alt' => 'close')) ?></a>
      <h1>
        <?php echo image_tag("logo-evenement.png",array('alt' => '')); ?>
        <?php echo $sf_response->getTitle() ?>
      </h1>
    </div>
    <div id="logo"></div>
    <div id="footer">
      <?php include_partial('global/footer') ?>
      <?php include_partial('global/date') ?>
    </div>
    <div id="transition"><span class="close"></span></div>
  </body>
</html>
