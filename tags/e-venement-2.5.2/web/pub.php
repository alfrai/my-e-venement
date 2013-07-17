<?php


require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('pub', 'prod', false);
$configuration->shut();
sfContext::createInstance($configuration)->dispatch();
