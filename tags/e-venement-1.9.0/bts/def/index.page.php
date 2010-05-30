<?php
	global $user, $config;
	if ( $user->hasRight($config["right"]["param"]) )
	{
?>
<li>
	Gestionnaire de tickets
	<ul>
		<li><a href="bts/def/rights.php">Gestion des droits</a> sur le module</li>
	</ul>
</li>
<?php	} ?>
