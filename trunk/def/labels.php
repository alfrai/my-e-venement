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
*    Copyright (c) 2006-2009 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	require("conf.inc.php");
	includeClass("bdRequest");
	
	$css[] = 'styles/labels.css';
	
	includeLib("headers");
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	$onglet = 'Étiquettes';
	$texte  = "Définissez les paramètres pour l'impression d'étiquettes.";
	$fields = array(
    'labels.width'     => array('210','mm'),
    'labels.height'     => array('297','mm'),
    'labels.nb-x'      => array('2',''),
    'labels.nb-y'      => array('7',''),
    'labels.top-bottom'=> array('15','mm'),
    'labels.left-right'=> array('4','mm'),
    'labels.printer-x' => array('14','mm'),
    'labels.printer-y' => array('12','mm'),
    'labels.margin-x'  => array('3','mm'),
    'labels.margin-y'  => array('0','mm'),
    'labels.padding-x' => array('2.5','mm'),
    'labels.padding-y' => array('1.5','mm'),
  );
	
	$bd->beginTransaction();
	if ( is_array($labels = $_POST['labels']) )
	foreach ( $fields as $key => $value)
	  $bd->addOrUpdateRecord(
	    'options',
	    array('key' => $key),
	    array('key' => $key, 'value' => $labels[$key] ? $labels[$key] : $value[0])
	  );
	if ( !$bd->getTransactionStatus() )
	  $user->addAlert("Impossible de mettre à jour vos paramètres d'étiquettes");
	$bd->endTransaction();
	
	$params = array();
	$query  = " SELECT * FROM options WHERE key LIKE 'labels.%'";
	$request = new bdRequest($bd,$query);
	while ( $rec = $request->getRecordNext() )
	  $params[$rec['key']] = $rec['value'];
	$request->free();
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="def/">Paramétrage</a><a class="nohref active"><?php echo htmlsecure($onglet) ?></a><a href="." class="parent">..</a></p>
<div class="body">
<h2><?php echo $texte ?></h2>
<form name="formu" class="param" action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
  <div>
    <p>Vos paramètres:</p>
    <ul>
      <?php foreach ( $fields as $key => $value ): ?>
      <li>
        <span class="name"><?php echo htmlsecure(substr($key,7)) ?>:</span>
        <span class="value"><input
          type="text"
          name="labels[<?php echo htmlsecure($key) ?>]"
          value="<?php echo htmlsecure($params[$key] ? $params[$key] : $value[0]) ?>"
        /><?php echo htmlsecure($value[1]) ?></span>
        <span class="defaults">(default: <?php echo htmlsecure($value[0].$value[1]) ?>)</span>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <p><span><input type="submit" name="submit" value="valider" /></span></p>
</form>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
