<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	// $categories: l'acces à la table org_categorie
	// $organismes: l'accès à la table organisme_categorie
	// $orgid: l'id de l'organisme courant
	
	global $categories, $organismes, $orgid;
	global $action, $actions, $onchange;
	global $default, $config, $mod;
	
	// les categories
	echo '<select name="org[cat][][value]" class="cats" onchange="javascript: annu_orgcategorie(this.parentNode);">';
	echo '<option value="">-toute catégorie-</option>';
	echo '<option value="NULL">-sans catégorie-</option>';
	$categories->firstRecord();
	while ( $cat = $categories->getRecordNext() )
		echo '<option value="'.intval($cat["id"]).'">'.htmlsecure($cat["libelle"]).'</option>';
	echo '</select>';
	echo '<a href="org/fiche.php?add">Nouvel organisme</a>';
	echo '<span class="desc">'.htmlsecure($default["opennewpage"]).'</span>';
	
	// les organismes
	if ( !isset($mod) || $mod !== false )
	{
		echo '<select name="org[org][][value]" class="orgs" onchange="'.$onchange.'">';
			echo '<option value="">-les organismes-</option>';
			$organismes->firstRecord();
			while ( $org = $organismes->getRecordNext() )
			{
				echo '<option value="'.intval($org['id']).'"';
				echo $org['id'] == $orgid ? ' selected="selected">' : '>';
				echo htmlsecure($org['nom'].' ('.$org["catdesc"].($org["catdesc"] && $org["ville"] ? ' - ' : '').$org["ville"].')');
				echo '</option>';
			}
		echo '</select>';
	}
	else
	{
		while ( $org = $organismes->getRecordNext() )
		if ( $org['id'] == $orgid )
		echo htmlsecure($org['nom'].' ('.$org["catdesc"].($org["catdesc"] && $org["ville"] ? ' - ' : '').$org["ville"].')');
	}	
?>
