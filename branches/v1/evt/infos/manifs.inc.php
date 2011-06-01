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
			<?php if ( $action != $actions["view"] ) { ?>
			<p class="add">
				<span class="cell">
					<input type="button" onclick="javascript: ttt_addmanif(document.getElementById('manifmodel'));" value="+" name="add"/>
					<span class="desc">nouvelle manifestation</span>
					<input type="hidden" name="manif[delmanif][][value]" id="delmanif" value="" />
				</span>
			</p>
			<?php } ?>
			<?php
				if ( $action == $actions["add"] ) $query = NULL;
				$manifestations = new bdRequest($bd,$query);
				for ( $i = 0 ; ( $i == 0 && $action != $actions["view"] ) | ( $manif = $manifestations->getRecordNext() ) ; $i++ )
				{
					echo '<div '.($i == 0 ? 'id="manifmodel"' : '').'>';
					echo '<a name="manif'.$manif["id"].'"></a>';
					echo '<input type="hidden" name="manif[manifid][][value]" value="'.$manif["id"].'" />';
					echo "<p>";
						echo '<span class="cell">Date'.($action != $actions["view"] ? '*' : '').':</span>';
						echo '<span class="cell">';
						printField("manif[".($name = "date")."][]",$manif[$name],$default[$name],255,NULL,false,NULL,NULL,false);
						echo '</span>';
					echo '</p><p>';
						echo '<span class="cell">Duree:</span>';
						echo '<span class="cell">';
						printField("manif[".($name = "duree")."][]",substr($manif[$name],0,5),$default[$name],255,NULL,false,NULL,NULL,false);
						echo '</span>';
					echo '</p><p>';
						echo '<span class="cell">Site'.($action != $actions["view"] ? '*' : '').':</span>';
						echo '<span class="cell">';
						if ( $action == $actions["view"] )
						{
							echo '<a href="evt/infos/salles.php?id='.$manif["siteid"].'&view">'.htmlsecure($manif["sitenom"]).'</a>';
							echo ' ('.htmlsecure($manif["siteville"]).')';
						}
						else
						{
							echo '<select name="manif[site][]">';
							echo '<option value="">-les lieux-</option>';
							
							$query	= " SELECT id, ville, nom
								    FROM site
								    WHERE active = 't'
								    ORDER BY ville, nom";
							$sites = new bdRequest($bd,$query);
								while ( $site = $sites->getRecordNext() )
								echo '<option value="'.intval($site["id"]).'" '.(intval($site["id"]) == intval($manif["siteid"]) ? 'selected="selected"' : '').'>'.htmlsecure($site["ville"]).' - '.htmlsecure($site["nom"]).'</option>';
							$sites->free();
							echo '</select>';
						}
						echo '</span>';
					echo '</p><p>';
						echo '<span class="cell">Jauge:</span>';
						echo '<span class="cell">';
						printField("manif[".($name = "jauge")."][]",$manif[$name],$default[$name],10,6,false,NULL,NULL,false);
						echo '</span>';
					echo '</p><p>';
						echo '<span class="cell">Description:</span>';
						echo '<span class="cell">';
						printField("manif[".($name = "description")."][]",$manif[$name],$default[$name],255,NULL,true,NULL,NULL,false);
						echo '</span>';
					if ( $action != $actions["view"] )
					{
						echo '</p><p class="del">';
							echo '<span class="cell">';
							echo '<input type="button" onclick="javascript: '."ttt_delmanif(this.parentNode.parentNode.parentNode);".'" value="-" name="del"/>';
							echo '<span class="desc">retirer cette manifestation</span>';
							echo '</span>';
					}
					echo "</p></div>";
				}
			?>
