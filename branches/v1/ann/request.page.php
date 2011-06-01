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
	global $user, $config, $bd;
	
	if ( $user->hasRight($config["right"]["devel"]) )
	{
?>
	<h2>Requêteur SQL</h2>
	<form action="?sql" method="post" class="sql"> 
		<p><textarea name="req" cols="70" rows="8"><?php echo htmlsecure($_POST["req"]) ?></textarea></p>
		<p><input type="submit" name="submit" value="ok" /></p>
	</form>
	
	<?php
		$query = $_POST["req"];
		if ( trim($query) )
		{
			$request = new bdRequest($bd,$query);
			if ( !$request->hasFailed() )
			{
				echo '<form method="post" action="ann/sqlcsv.php">';
				echo '<p>';
				echo '<input type="submit" name="export" value="Exporter le résultat" /><input type="hidden" name="req" value="'.htmlsecure($query).'" />';
				echo '<input type="checkbox" name="msexcel" value="yes" /> Compatibilité MSExcel';
				echo '</p>';
				echo '</form>';
	?>
	<table class="answer" border="1">
		<tr><?php foreach ( $request->getFields() as $key => $value ) { echo '<th>'.htmlsecure($key).'</th>'; } ?></tr>
		<?php
				while ( $rec = $request->getRecordNext() )
				{
					echo '<tr>';
					foreach ( $rec as $value )
						echo '<td>'.htmlsecure($value).'</td>';
					echo '</tr>';
				}
		?>
	</table>
	<p><?php echo htmlsecure($request->countRecords()).' résultat(s)' ?></p>
<?php
			} // if ( !$request->hasFailed() )
			$request->free();
		} // if ( trim($query) )
	}
?>
