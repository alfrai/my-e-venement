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
	require("conf.inc.php");
	includeClass("bdRequest/array");
	includeJS("ttt");
	includeJS("jquery");
	includeJS("new-search");
	
	$css = array($css,'styles/search.css');
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$action = $actions["edit"];
	$request = false;
	
	$grpid = intval($_GET['grpid']);
	
  // correspondance SQL / HTTP_POST
  $fields = array(
    'orgid'   => 'organisme',
    'fctid'   => 'fonction',
    'child.birth' => 'age',
    'cp'      => 'cp',
    'ville'   => 'ville',
    'grpinc'  => 'groupes',
    'npai'    => 'npai',
    'email'   => 'email',
    'adresse' => 'adresse',
    'creation'=> 'creation',
    'modification' => 'modification',
    'orgcat'  => 'orgcat',
    'description' => 'description'
  );
  
  $realfields = $fields;
  unset($realfields['creation']);
  unset($realfields['modification']);
  unset($realfields['child.birth']);
  $realfields['infcreation'] = 'creation[inf]';
  $realfields['infmodification'] = 'modification[inf]';
  $realfields['supcreation'] = 'creation[sup]';
  $realfields['supmodification'] = 'modification[sup]';
  $realfields['childmin'] = 'age[min]';
  $realfields['childmax'] = 'age[max]';
  
  includeLib("headers");
  
  // saving groups
  if ( $_POST['save'] && $user->hasRight($config['right']['group']) )
  if ( is_array($tmp = $_POST['grp']) )
  if ( $tmp['nom'] )
  {
    $grp['nom']      = $tmp['nom'];
    if ( !isset($tmp['common']) || !$user->hasRight($config["right"]["commongrp"]) )
      $grp['createur'] = $user->getId();
    if ( isset($tmp['clean']) )
      $bd->delRecordsSimple('groupe',array('id' => $grpid) );
    if ( $bd->addRecord('groupe',$grp) !== false )
    {
      $grpid = $bd->getLastSerial('groupe','id');
      if ( $grpid > 0 && $tmp['dynamic'] && is_array($_POST['criterias']) && count($_POST['criterias']) > 0 )
      {
        // groupe dynamique
        $rec = array('groupid' => $grpid);
        foreach ( $realfields as $sql => $post )
          $rec[$sql] = $_POST['criterias'][$sql];
        $bd->addRecord('groupe_andreq',$rec);
      }
      else
      {
        // groupe static
        $arr = array(
          'fctorgid' => array('table' => 'groupe_fonctions', 'sql' => 'fonctionid'),
          'persid'   => array('table' => 'groupe_personnes', 'sql' => 'personneid'),
        );
        foreach ( $arr as $name => $type ) 
        if ( is_array($_POST['save'][$name]) )
        foreach ( $_POST['save'][$name] as $tmpid )
        if ( intval($tmpid) > 0 )
          $bd->addRecord($type['table'],array(
            'groupid' => $grpid,
            $type['sql'] => intval($tmpid),
            'included' => 't',
          ));
      }
    }
    else $user->addAlert('Impossible de créer votre groupe, son nom est peut-être déjà utilisé.');
  }
  
  // remove chosen members
  $remove = array('perso' => array(), 'pro' => array());
  if ( is_array($_POST['rem-members']) && $grpid )
  {
    foreach ( $_POST['rem-members']['pro'] as $value )
        $remove['pro'][]    = intval(substr($value,5));
    foreach ( $_POST['rem-members']['perso'] as $value )
      $remove['perso'][]  = intval(substr($value,5));
    $bd->delRecords('groupe_fonctions','fonctionid IN ('.implode(',',$remove['pro']).')');
    $bd->delRecords('groupe_personnes','personneid IN ('.implode(',',$remove['perso']).')');
  }
  
  // search
  if ( isset($_POST['search']) )
  {
    $where = $criterias = array();
    foreach ( $fields as $sql => $post )
    if ( $_POST[$post] )
    switch ( $sql ) {
    case 'child.birth':
      if ( floatval($_POST[$post]['max']) > 0 )
      {
        $criterias['childmax'] = floatval($_POST[$post]['max']);
        $where[] = '       '.$sql.' - '.date('Y').' =< '.floatval($_POST[$post]['max']);
      }
      if ( floatval($_POST[$post]['min']) > 0 )
      {
        $criterias['childmin'] = floatval($_POST[$post]['min']);
        $where[] = '       '.$sql.' - '.date('Y').' >= '.floatval($_POST[$post]['min']);
      }
      break;
    case 'ville':
    case 'cp':
      if ( $_POST[$post] )
      $criterias[$sql] = $_POST[$post];
      $where[]   = '       ('.$sql." ILIKE '".pg_escape_string($_POST[$post])."%' OR org".$sql." ILIKE '".pg_escape_string($_POST[$post])."%')";
      break;
    case 'npai':
      if ( isset($_POST[$post]) )
      {
        $criterias[$sql] = true;
        $where[]   = '       '.$sql;
      }
      else  $criterias[$sql] = false;
      break;
    case 'email':
      if ( isset($_POST[$post]) )
        $where[]   = 'pro'.$sql.' IS NULL';
    case 'adresse':
      if ( isset($_POST[$post]) )
      {
        $criterias[$sql] = true;
        $where[]   = '       '.$sql.' IS NULL';
        $where[]   = 'org'.$sql.' IS NULL';
      }
      else  $criterias[$sql] = false;
      break;
    case 'creation':  
    case 'modification':
      if ( $_POST[$post]['inf'] )
      {
        $criterias['inf'.$sql] = $_POST[$post]['inf'];
        $where[]   = '       '.$sql." >= '".date('Y-m-d',strtotime($_POST[$post]['inf']))."'";
      }
      if ( $_POST[$post]['sup'] )
      {
        $criterias['sup'.$sql] = $_POST[$post]['sup'];
        $where[]   = '       '.$sql." <= '".date('Y-m-d',strtotime($_POST[$post]['sup']))."'";
      }
      break;
    case 'description':
      $tmp = array();
      foreach ( explode(' ', $_POST[$post]) as $keyword )
        $tmp[] = "' '||".$sql."||' ' ILIKE '% ".pg_escape_string(trim($keyword))." %'";
      $where[] = '('.implode(' OR ',$tmp).')';
      break;
    case 'grpinc':
      if ( is_array($_POST[$post]) && count($_POST[$post]) > 0 )
      {
        $criterias['grpinc'] = '{'.implode(',',$_POST[$post]).'}';
        $where[]   = '       ( p.id IN (SELECT personneid
                                        FROM groupe_personnes
                                        WHERE groupid IN ('.implode(',',$_POST[$post]).')) AND p.fctorgid IS NULL
                            OR p.fctorgid IN (SELECT fonctionid
                                              FROM groupe_fonctions
                                              WHERE groupid IN ('.implode(',',$_POST[$post]).'))
                             )';
      }
      break;
    default:
      if ( intval($_POST[$post]) > 0 )
      {
        $criterias[$sql] = $_POST[$post];
        $where[]   = '       '.$sql.' = '.intval($_POST[$post]);
      }
      break;
    }
    
    if ( count($where) > 0 )
    {
      $query  = ' SELECT p.*
                  FROM personne_properso p LEFT JOIN child ON child.personneid = p.id
                  WHERE '.implode(' AND ',$where).'
                  ORDER BY nom, prenom, orgnom, orgville, ville';
      $request = new bdRequest($bd,$query);
    }
  }
  
  // retrieve group's name
  if ( $grpid > 0 )
  {
    $query = ' SELECT nom, id IN ( SELECT groupid FROM groupe_andreq ) AS dynamic FROM groupe WHERE id = '.$grpid;
    $groupe = new bdRequest($bd,$query);
    $grpname = $groupe->getRecord('nom');
    $grpdyn = $groupe->getRecord('dynamic') == 't';
    $groupe->free();
  }
  else if ( isset($_POST['remove']) )
  {
    $pro = $perso = array();
    if ( is_array($_POST['members']['pro']) )
    foreach ( $_POST['members']['pro'] as $value )
      $pro[intval($value)] = intval($value);
    if ( is_array($_POST['members']['perso']) )
    foreach ( $_POST['members']['perso'] as $value )
      $perso[intval($value)] = intval($value);
    
    if ( is_array($_POST['rem-members']['pro']) )
    foreach( $_POST['rem-members']['pro'] as $value )
      unset($pro[intval($value)]);
    if ( is_array($_POST['rem-members']['perso']) )
    foreach( $_POST['rem-members']['perso'] as $value )
      unset($perso[intval($value)]);
    
    $where = array();
    if ( count($perso) > 0 )
    $where[] = 'id       IN ('.implode(',', $perso).') AND fctorgid IS NULL';
    if ( count($pro)   > 0 )
    $where[] = 'fctorgid IN ('.implode(',', $pro  ).')';
    $query  = ' SELECT p.*
                FROM personne_properso p
                WHERE '.implode(' OR ',$where).'
                ORDER BY nom, prenom, orgnom, orgville, ville';
    $request = new bdRequest($bd,$query);
  }
?>
<?php if ( $grpdyn ): ?>
<?php
  $query = ' SELECT * FROM groupe_andreq WHERE groupid = '.$grpid;
  $dyn = new arrayBdRequest($bd,$query);
?>
<script type="text/javascript">
$(document).ready(function(){
  $.post('<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>',{
    <?php
      foreach ( $realfields as $sql => $post )
      if ( $dyn->getRecord($sql) )
      switch ( $sql ) {
      case 'grpinc':
        foreach ( $dyn->getRecord($sql) as $value )
        echo "'".htmlsecure($post)."[]': ".intval($value).',';
        break;
      case 'email':
      case 'adresse':
      case 'npai':
        if ( $dyn->getRecord($sql) == 't' )
          echo htmlsecure($post).": 'yes',";
        break;
      default:
    ?>
    '<?php echo htmlsecure($post) ?>': '<?php echo htmlsecure($dyn->getRecord($sql)) ?>',
  <?php
      break;
    }
  ?>
    search: 'rechercher',
  },function(data){
    $('.result').html($(data).find('.result'));
  },'html');
});
</script>
<?php $dyn->free(); ?>
<?php endif; ?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require('actions.php'); ?>
<div class="body">
<h2><?php echo htmlsecure($grpname ? 'Groupe : '.$grpname : 'Recherche et groupes') ?></h2>
<?php if ( $grpid == 0 && !$request ): ?>
<form action="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>" method="post" class="search">
<div class="criterias">
  <p class="titre">Critères de recherche</p>
  <?php
    $query  = ' SELECT *
                FROM fonction
                ORDER BY libelle';
    $fonctions = new bdRequest($bd,$query);
  ?>
  <div>
  <span class="fonction">
    <select name="fonction">
      <option>-les fonctions-</option>
    <?php while ( $rec = $fonctions->getRecordNext() ): ?>
      <option value="<?php echo intval($rec['id']) ?>"><?php echo htmlsecure($rec['libelle']) ?></option>
    <?php endwhile; ?>
    </select>
  </span>
  <?php $fonctions->free(); ?>
  <span class="and">ET</span>
  <span class="organisme">
    org: 
    <input type="hidden" name="organisme" value="" />
    <input type="text" name="ajax[organisme]" value="" title="Saisissez le début du nom que vous recherchez et appuyez sur Entrée." />
  </span>
  <ul class="organismes noborder"></ul>
  <span class="and">ET</span>
  <span class="orgcat">
    <?php
      $query = ' SELECT * FROM org_categorie ORDER BY libelle';
      $orgcat = new bdRequest($bd,$query);
    ?>
    <select name="orgcat">
      <option value="">-Catégories d'org.-</option>
      <?php while($cat = $orgcat->getRecordNext() ): ?>
      <option value="<?php echo intval($cat['id']) ?>"><?php echo htmlsecure($cat['libelle']) ?></option>
      <?php endwhile; ?>
    </select>
    <?php $orgcat->free(); ?>
  </span>
  <?php
    $query  = ' SELECT g.id, g.nom, a.name AS owner, a.id AS ownerid, a.id IS NULL AS common, a.id = '.$user->getId().' AS me
                FROM groupe g LEFT JOIN account a ON a.id = g.createur
                ORDER BY common DESC, me DESC, owner, ownerid, nom';
    $groupes = new bdRequest($bd,$query);
    $owner = false;
  ?>
  <span class="and">ET</span>
  <span class="groupes">
    <select name="groupes[]" multiple="multiple" title="groupe n°1 OU groupe n°2 OU groupe n°3">
      <optgroup label="Groupes communs">
    <?php while ( $rec = $groupes->getRecordNext() ): ?>
      <?php if ( $rec['ownerid'] != $ownerid ): ?>
      <?php $ownerid = intval($rec['ownerid']); ?>
      </optgroup><optgroup label="<?php echo htmlsecure($rec['ownerid'] == $user->getId() ? 'Mes groupes' : $rec['owner']) ?>">
      <?php endif; ?>
      <option value="<?php echo intval($rec['id']) ?>"><?php echo htmlsecure($rec['nom']) ?></option>
    <?php endwhile; ?>
      </optgroup>
    </select>
  </span>
  <?php $groupes->free(); ?>
  </div>
  <div>
  <span class="enfants">
    <input type="text" name="age[min]" value="" />
    &lt;= âge =&gt;
    <input type="text" name="age[max]" value="" />
  </span>
  <span class="and">ET</span>
  <span class="cp">
    Code Postal:
    <input type="text" name="cp" value="" />
  </span>
  <span class="and">ET</span>
  <span class="ville">
    Ville:
    <input type="text" name="ville" value="" />
  </span>
  </div>
  <div>
  <span class="keywords">
    Mots clés:
    <input type="text" name="description" value="" size="50" />
    (ex: "associatif élu" => tous ceux qui sont associatifs OU élu)
  </span>
  </div>
  <div class="options">
  <p class="display off"><span class="plus">plus d'options</span><span class="moins">moins d'options</span></p>
  <p class="npai">NPAI ? <input type="checkbox" name="npai" value="yes" /></p>
  <p class="email">Pas d'email ? <input type="checkbox" name="email" value="no" /></p>
  <p class="adresse">Pas d'adresse ? <input type="checkbox" name="adresse" value="no" /></p>
  <div class="date">
    <p class="sup">
      <span class="title">Modification :</span> 
      <span><input type="text" name="modification[inf]" value="" /></span>
      <span>&lt;= date =&gt;</span>
      <span><input type="text" name="modification[sup]" value="" /></span>
    </p>
    <p class="inf">
      <span class="title">Creation :</span>
      <span><input type="text" name="creation[inf]" value="" /></span>
      <span>&lt;= date =&gt;</span>
      <span><input type="text" name="creation[sup]" value="" /></span>
    </p>
  </div>
  </div>
</div>
<p class="submit"><input type="submit" name="search" value="rechercher" /></p>
</form>
<?php else: ?>
<form action="<?php echo htmlsecure($_SERVER['PHP_SELF'].'?grpid='.$grpid) ?>" method="post" class="result">
<h2>Le résultat de la recherche</h2>
<p class="checkall"><input type="checkbox" class="checkall" name="checkall" value="" title="inverser la sélection" />
<ul class="grpmembers">
<?php
  if ( !$request )
  {
    if ( !$grpdyn )
    // show group content
	  $query  = '(SELECT p.*
	              FROM groupe_personnes gp, personne_properso p
	              WHERE gp.groupid = '.$grpid.'
	                AND p.id = gp.personneid AND p.fctorgid IS NULL
	                AND included
	              ) UNION (
	              SELECT p.*
	              FROM groupe_fonctions gf, personne_properso p
	              WHERE gf.groupid = '.$grpid.'
	                AND p.fctorgid = gf.fonctionid
	                AND included )
	             ORDER BY nom, prenom, orgnom, fctdesc, fcttype';
	  $request = new bdRequest($bd,$query);
  }
?>
<?php while ( $rec = $request->getRecordNext() ): ?>
  <li>
    <input type="checkbox" name="rem-members[<?php echo intval($rec['fctorgid']) > 0 ? 'pro' : 'perso' ?>][]" value="<?php echo intval($rec['fctorgid']) > 0 ? $rec['fctorgid'] : $rec['id'] ?>" class="member" />
    <input type="hidden"   name="members[<?php echo intval($rec['fctorgid']) > 0 ? 'pro' : 'perso' ?>][]"    value="<?php echo intval($rec['fctorgid']) > 0 ? $rec['fctorgid'] : $rec['id'] ?>" />
    <a href="ann/fiche.php?id=<?php echo $rec['id'] ?>"><?php echo htmlsecure($rec['nom'].' '.$rec['prenom']) ?></a>
    <?php if ( intval($rec['orgid']) > 0 ): ?>
    (<a href="org/fiche.php?id=<?php echo $rec['orgid'] ?>"><?php echo htmlsecure($rec['orgnom']) ?></a> - <?php echo htmlsecure($rec['fctdesc']) ?>)
    <?php endif; ?>
  </li>
<?php endwhile; ?>
</ul>
<p class="nb"><?php echo intval($request->countRecords()) ?> résultat(s)</p>
<p class="submit"><input type="submit" name="remove" value="retirer" /></p>
</form>
<?php if ( $user->hasRight($config['right']['group']) ): ?>
<hr/>
<form method="post" action="<?php echo htmlsecure($_SERVER['PHP_SELF'].'?grpid='.$grpid) ?>" class="save">
  <h2>Enregistrer en tant que groupe...</h2>
  <p class="name">Nom: <input type="input" name="grp[nom]" value="" /></p>
  <?php if ( $user->hasRight($config["right"]["commongrp"]) ): ?>
  <p class="opt"><input type="checkbox" name="grp[common]" value="yes" /> Groupe commun</p>
  <p class="opt"><input type="checkbox" name="grp[clean]" value="yes" /> Autoriser le remplacement du groupe actuel (le renommer)</p>
  <?php endif; ?>
  <?php if ( $_POST['search'] ): ?>
  <p class="opt"><input type="checkbox" name="grp[dynamic]" value="yes" /> Enregistrement "dynamique"</p>
  <?php endif; ?>
  <p class="rec"><input type="submit" name="save" value="Enregistrer" />
  <div class="hidden">
	  <?php $request->firstRecord(); while ( $rec = $request->getRecordNext() ): ?>
    <input type="hidden"
      name="save[<?php echo htmlsecure(intval($rec['fctorgid']) > 0 ? 'fctorgid' : 'persid') ?>][]"
      value="<?php echo htmlsecure(intval($rec['fctorgid']) > 0 ? $rec['fctorgid'] : $rec['id']) ?>"
    />
    <?php endwhile; ?>
    <?php if ( is_array($criterias) ): foreach ( $criterias as $key => $value ): ?>
    <input type="hidden" name="criterias[<?php echo htmlsecure($key) ?>]" value="<?php echo htmlsecure($value === true ? 't' : $value) ?>" />
    <?php endforeach; endif; ?>
  </div>
</form>
<?php endif; ?>
<?php
  $query	= " SELECT value
	            FROM options
              WHERE accountid = ".$user->getId()."
                AND key = 'ann.extractor'";
  $req = new bdRequest($bd,$query);
  $presel = split(';',$req->getRecord("value"));
  $req->free();
?>
<hr/>
<form method="post" action="<?php echo htmlsecure('ann/extract.php?'.$qstring) ?>" class="extractor" target="_blank">
	<h2>Extraire...</h2>
	<div>
		<p class="perso">
			<span class="titre">Données personnelles</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" <?php if ( in_array("titre",$presel) ) echo 'checked="checked"' ?> name="csv[fields][]" value="titre" onclick="javascript: ttt_spanCheckBox(this);"/>
				Titre
			</span>
			<span class="onclick">
				<input type="checkbox" checked="checked" name="csv[fields][]" value="nom" disabled="disabled"/>
				<input type="hidden" name="csv[fields][]" value="nom" />
				Nom
			</span>
			<span class="onclick">
				<input type="checkbox" checked="checked" name="csv[fields][]" value="prenom" disabled="disabled"/>
				<input type="hidden" name="csv[fields][]" value="prenom" />
				Prénom
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="adresse" <?php if ( in_array("adresse",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Adresse
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="cp" <?php if ( in_array("cp",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Code Postal
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="ville" <?php if ( in_array("ville",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Ville
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="pays" <?php if ( in_array("pays",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Pays
			</span>
			<span class="onclick npai" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="npai" <?php if ( in_array("npai",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				NPAI
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" <?php if ( in_array("email",$presel) ) echo 'checked="checked"' ?> value="email" onclick="javascript: ttt_spanCheckBox(this);"/>
				e-mail
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" <?php if ( in_array("telnum",$presel) ) echo 'checked="checked"' ?> value="telnum" onclick="javascript: ttt_spanCheckBox(this);"/>
				Numéro de téléphone (le premier saisi)
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="teltype" <?php if ( in_array("teltype",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Type de téléphone (le premier saisi)
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="creation" <?php if ( in_array("creation",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Date de création
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="modification" <?php if ( in_array("modification",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Date de modification
			</span>
		</p>
		<p class="properso">
			<span class="titre">Données liées à l'organisme</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="protel" <?php if ( in_array("protel",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Téléphone professionnel
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="proemail" <?php if ( in_array("proemail",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				e-mail professionnel
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="fcttype" <?php if ( in_array("fcttype",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Fonction type
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="fctdesc" <?php if ( in_array("fctdesc",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Fonction personnalisée
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="service" <?php if ( in_array("service",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Service
			</span>
		</p>
		<p class="pro">
			<span class="titre">Données de l'organisme</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgcat" <?php if ( in_array("orgcat",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Catégorie
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgnom" <?php if ( in_array("orgnom",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Nom
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgadr" <?php if ( in_array("orgadr",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Adresse
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgcp" <?php if ( in_array("orgcp",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Code Postal
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgville" <?php if ( in_array("orgville",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Ville
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgpays" <?php if ( in_array("orgpays",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Pays
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgemail" <?php if ( in_array("orgemail",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				e-mail
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgurl" <?php if ( in_array("orgurl",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Site Internet
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgdesc" <?php if ( in_array("orgdesc",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Description
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgtelnum" <?php if ( in_array("orgtelnum",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Numéro de téléphone (le premier saisi)
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgteltype" <?php if ( in_array("orgteltype",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Type de téléphone (le premier saisi)
			</span>
		</p>
		<p class="other">
			<span class="titre">Autres</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="info" <?php if ( in_array("info",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Informations complémentaires contextuelles
			</span>
		</p>
		<p class="system">
			<span class="titre">Options de l'export</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="printfields" value="yes" <?php if ( in_array("printfields",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Afficher le nom des champs
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="entonnoir" value="yes" <?php if ( in_array("entonnoir",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Préférer les données professionnelles
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="msexcel" value="yes" <?php if ( in_array("msexcel",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Compatibilité MSExcel au détriment des normes
			</span>
		</p>
	</div>
	<p class="hidden"><?php $request->firstRecord(); while ( $rec = $request->getRecordNext() ): ?>
    <input type="hidden"
      name="csv[<?php echo htmlsecure(intval($rec['fctorgid']) > 0 ? 'fctorgid' : 'persid') ?>][]"
      value="<?php echo htmlsecure(intval($rec['fctorgid']) > 0 ? $rec['fctorgid'] : $rec['id']) ?>"
    />
	<?php endwhile; ?></p>
	<p>
	  <input type="submit" name="submit" value="Extraire" />
	  <input type="submit" name="labels" value="Étiquettes" />
	</p>
</form>
<?php $request->free(); ?>
<?php endif; ?>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
