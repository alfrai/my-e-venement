<?php
	global $user, $config;
	require_once($_SERVER["DOCUMENT_ROOT"].$config["website"]["root"]."sco/secu.php");
	if ( $user->scolevel >= $config["sco"]["right"]["param"] || $user->hasRight($config["right"]["param"]) )
	{
?>
<li>
	Scolaires & Groupes
	<ul>
		<li><a href="sco/def/params.php">Paramétrage général</a> du module</li>
		<li><a href="sco/def/rights.php">Gestion des droits</a> sur ce module</li>
	</ul>
</li>
<?php	} ?>
