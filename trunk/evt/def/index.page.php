<?php include(dirname(__FILE__).'/conf.inc.php'); ?>
<li>
	Évènements
	<ul>
		<?php if ( $config['evt']['spaces'] ): ?>
		  <li><a href="evt/def/space.php">Espaces</a> de travail</li>
		<?php endif; ?>
		<li><a href="evt/def/rights.php">Gestion des droits</a> sur le module</li>
		<li><a href="evt/def/catevt.php">Les catégories d'évènements</a></li>
		<li><a href="evt/def/tarif.php">Les tarifs</a> par défaut</li>
		<?php if ( $config['ticket']['cat-tarifs'] ): ?>
		<li><a href="evt/def/cat-tarifs.php">Les groupes de tarifs</a> (tableaux de tarifs)</li>
		<?php endif; ?>
		<li><a href="evt/def/modepaiement.php">Les modes de paiement</a> acceptés</li>
		<li><a href="evt/def/colors.php">Définir des couleurs</a> pour les manifestations</li>
		<li><a href="evt/def/metaevt.php">Les meta-évènements</a></li>
	</ul>
</li>
