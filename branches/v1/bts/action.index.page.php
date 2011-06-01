<?php
	global $user,$config;
	require_once("config.php");
	
	if ( $user->hasRight($config["rights"]["param"]) )
	{
?>
<a class="add" href="bts/" title="Préférez son ouverture dans un nouvel onglet" target="_blank">Tickets</a>
<?php	} ?>
