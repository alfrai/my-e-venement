<?php
	global $user,$config;
	require_once("config.php");
	require_once("secu.php");
	
	if ( $user->vellevel >= $config["vel"]["right"]["mod"] )
	{
?>
<a class="add" href="vel/def/" title="Accès direct à l'administration">VeL</a>
<?php	} ?>
