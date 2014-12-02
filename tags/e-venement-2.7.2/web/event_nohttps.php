<?php

if ( $_SERVER['PATH_INFO'] != '/event/calendar' )
  die('You are not allowed to access this file. Check '.str_replace('_nohttps','',basename(__FILE__)).' for more information.');

$_GET['nourl'] = true;
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('event', 'prod', false);
sfContext::createInstance($configuration)->dispatch();
