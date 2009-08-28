<?php
	global $user,$config;
	require_once("pro/config.php");
	require_once("pro/secu.php");
	
	if ( $user->prolevel >= $config["pro"]["right"]["view"] )
	{
?>
<a class="add" href="pro/">Pros</a>
<?php	} ?>
