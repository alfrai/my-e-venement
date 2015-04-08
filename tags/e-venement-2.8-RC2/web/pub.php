<?php

header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('pub', 'prod', false);

sfConfig::set('app_user_session_ns', 'pub');
$configuration->shut();

sfContext::createInstance($configuration)->dispatch();
