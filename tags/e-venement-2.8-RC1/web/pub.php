<?php

header('P3P: CP="Topinambour"');
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('pub', 'prod', false);

sfConfig::set('app_user_session_ns', 'pub');
$configuration->shut();

sfContext::createInstance($configuration)->dispatch();
