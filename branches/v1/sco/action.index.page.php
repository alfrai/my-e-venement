<?php
	global $user, $config;
	require_once("sco/config.php");
  //require_once("sco/secu.php");
	
	if ( $user->scolevel >= $config["sco"]["right"]["view"] )
		echo '<a class="add" href="sco/">Sco & Grp</a>'
?>
