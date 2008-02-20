<?php
	global $user, $config;
	require_once($_SERVER["DOCUMENT_ROOT"].$config["website"]["root"]."vel/secu.php");
	if ( $user->prolevel >= $config["vel"]["right"]["mod"] || $user->hasRight($config["right"]["param"]) )
	{
?>
<li>
	Vente en ligne
	<ul>
		<?php if ( $user->prolevel >= $config["vel"]["right"]["param"] || $user->hasRight($config["right"]["param"]) ) { ?>
		<li><a href="vel/def/rights.php">Gestion des droits</a> sur le module</li>
		<?php } ?>
		<li><a href="vel/def/params.php">Paramétrage général</a> du module</li>
		<li><a href="vel/def/manifs.php">Gestion des manifestations</a> accessibles à la vente en ligne</li>
		<li><a href="vel/def/tarifs.php">Gestion des tarifs</a> accessibles à la vente en ligne</li>
	</ul>
</li>
<?php	} ?>
