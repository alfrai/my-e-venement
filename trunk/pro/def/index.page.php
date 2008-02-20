<?php
	global $user, $config;
	require_once($_SERVER["DOCUMENT_ROOT"].$config["website"]["root"]."pro/secu.php");
	if ( $user->prolevel >= $config["pro"]["right"]["param"] || $user->hasRight($config["right"]["param"]) )
	{
?>
<li>
	Professionnels
	<ul>
		<li><a href="pro/def/params.php">Paramétrage général</a> du module</li>
		<li><a href="pro/def/rights.php">Gestion des droits</a> sur les professionnels</li>
		<li><a href="pro/def/modepaiement.php">Paramétrage des modes de paiement</a> des professionnels</li>
		<li><a href="pro/def/contingents.php">Paramétrage des personnes</a> dont les contingents sont à prendre en compte</li>
	</ul>
</li>
<?php	} ?>
