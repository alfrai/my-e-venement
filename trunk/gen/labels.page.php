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
  global $user, $bd, $arr, $params, $class, $css, $title;
  $class = 'labels';
	$css = array('styles/main.css','styles/labels.css.php','styles/labels.css');
	$title = "e-venement : étiquettes";

	$level	= $config["right"]["view"];
	
	includeLib("login-check");
	includeLib("headers");
	
	if ( !isset($_SESSION['labels'] )
	{
	  $_SESSION['labels'] = true;
	  $user->addAlert("Attention, l'imprimante à utiliser doit avoir des marges de 15mm x 12mm ; veillez à avoir bien paramétré Mozilla Firefox pour n'imprimer aucun entête ni pied de page, et d'avoir ses marges à 0");
	}
	
	$etiquettes = array();
	for ( $i = 0 ; $i < count($arr) ; $i++ )
	{
	  // make pages
	  if ( $i % (intval($params['nb-x'])*intval($params['nb-y'])) == 0 )
	    $etiquettes[] = array();
	  $nbpages = count($etiquettes);
	  
	  // make lines
	  if ( $i % intval($params['nb-x']) == 0 )
	    $etiquettes[$nbpages-1][] = array();
	  $nblines = count($etiquettes[$nbpages-1]);
	  
	  $etiquettes[$nbpages-1][$nblines-1][] = $arr[$i];
	}
	$i = count($etiquettes);
?>
<?php foreach ( $etiquettes as $page ): ?>
<div class="page <?php $i--; echo $i == 0 ? 'last-child' : ''; ?>"><ul class="labels">
  <?php foreach ( $page as $line ): ?>
  <li>
    <div><div class="content">
    <?php
      foreach ( $line as $key => $cell )
      {
        foreach ( $cell as $sql => $data )
          $cell[$sql] = htmlsecure($data);
        $line[$key] = '
          <p class="perso">
            <span class="titre">'.$cell['titre'].'</span>
            <span class="prenom">'.$cell['prenom'].'</span>
            <span class="nom">'.$cell['nom'].'</span>
          </p>
          <p class="pro">
            <span class="fonction">'.($cell['fctdesc'] ? $cell['fctdesc'] : $cell['fcttype']).'</span>
            <span class="service">'.$cell['service'].'</span>
          </p>
          <p class="org"><span class="nom">'.$cell['orgnom'].'</span></p>
          <p class="adresse">'.nl2br($cell['adresse']).'</p>
          <p class="ville"><span class="cp">'.$cell['cp'].'</span> <span class="ville">'.$cell['ville'].'</span></p>
          <p class="pays">'.$cell['pays'].'</p>
          <p class="email">'.$cell['email'].'</p>
          <p class="tels"><span class="direct">'.$cell['protel'].'</span>'.($cell['protel'] && $cell['telnum'] ? ' - ' : '').'<span class="telorg">'.$cell['telnum'].'</span></p>
        ';
        //$line[$key] = '<p>'.$cell['nom'].' '.$cell['prenom'].'</p>';
      }
    ?>
    <?php echo implode('</div></div><div class="margin"></div><div><div class="content">',$line) ?>
    </div>
  </li>
  <?php endforeach; ?>
</ul></div>
<?php endforeach; ?>
<?php
	includeLib("footer");
	$bd->free();
?>
