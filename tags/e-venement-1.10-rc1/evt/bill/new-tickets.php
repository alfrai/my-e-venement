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
  /**
    * Si le billet est imprimé en "hard" par le client, les codes de retour :
    *   fichier "BOCA": tout est ok
    *   1: erreur d'accès à la base de données
    *   2: problème lié par exemple au numéro de transaction
    *   3: problème d'écriture de fichiers
    *   4: problème de commandes inaccessibles (pour la conversion en PDF, voir /usr/bin/firefox, ou xvfb)
    *   5: problème de commandes inaccessibles (pour la conversion en fichier BOCA, voir pdftoraster et rastertoboca)
    * 254: pas de billet à imprimer
    * 255: pas les droits nécessaires
    *
    **/
  
  require('conf.inc.php');
  includeClass('tickets');
  
  if ( $user->evtlevel <= $config["evt"]["right"]["view"] )
  {
    if ( $config['print']['hard'] )
    {
      $bd->free();
      echo '255';
      beta_die(255);
    }
    else
    {
      $bd->free();
      $user->addAlert($msg = "Vous n'avez pas un niveau de droits suffisant pour accéder à cette fonctionnalité");
      $nav->redirect($config["website"]["base"]."evt/bill/",$msg);
    }
  }
  
  if ( isset($_GET['cancelprint']) && $salt && $config['print']['hard'] )
  {
    foreach ( $_SESSION['tickets'][$salt] as $ticketid )
      $bd->delRecordSimple('reservation_cur',array('id' => $ticketid));
    $bd->free();
    echo '0';
    beta_die(0);
  }
  
  // vérifs
  if ( ($transac = intval($_GET['transac'])) <= 0 )
  {
    if ( $config['print']['hard'] )
    {
      $bd->free();
      echo '2';
      beta_die(2);
    }
    else
    {
      $bd->free();
      $user->addAlert("Problème dans le numéro d'opération transmis au bon de commande.");
      $nav->redirect('.');
    }
  }
  
  $group    = isset($_GET['group']) && $config['ticket']['enable_group'];
  $vertical = $config['ticket']['enable_vertical'];
  $tarif    = isset($_GET['tarif']) ? $_GET['tarif'] : false;
  $manifid  = intval($_GET['manifid']) ? $_GET['manifid'] : false;
  $salt     = $_GET['salt'] && $config['print']['hard'] ? $_GET['salt'] : false;
  
  function verif_transaction()
  {
    global $bd,$user,$nav;
    if ( !$bd->getTransactionStatus() )
    {
      $bd->endTransaction();
      if ( $config['print']['hard'] )
      {
        $bd->free();
        echo '1';
        beta_die(1);
      }
      else
      {
        $bd->free();
        $user->addAlert('Impossible de retrouver les informations relatives au billet en base...');
        $nav->redirect('evt/bill/');
      }
    }
  }
  
  $bd->beginTransaction();
  
  // récup des infos sur la personne
  $query  = ' SELECT p.id, p.prenom, p.nom, p.adresse, p.cp, p.ville, p.pays, p.email
              FROM transaction AS t
              LEFT JOIN personne_properso p
                     ON p.id = t.personneid
                    AND ( p.fctorgid = t.fctorgid OR t.fctorgid IS NULL AND p.fctorgid IS NULL )
              WHERE t.id = '.$transac;
  $request = new bdRequest($bd,$query);
  $personne = $request->getRecord();
  $request->free();
  
  // canceling reservation_cur old tickets for duplicatas
  $duplicata = false;
  if ( $tarif && $manifid )
  {
    $where  = '     tm.id  = p.tarifid
                AND tm.manifid = p.manifid
                AND c.resa_preid = p.id
                AND NOT canceled
                AND p.transaction = '.$transac;
    if ( $tarif )
    $where .= " AND tm.key ILIKE '".pg_escape_string($tarif)."'";
    if ( $manifid )
    $where .= ' AND p.manifid = '.$manifid;
    $using  = 'reservation_pre p, tarif_manif tm';
    $query = ' SELECT count(*) AS nb
               FROM reservation_cur c, '.$using.'
               WHERE '.$where;
    $request = new bdRequest($bd,$query);
    $existing = $request->getRecord('nb'); // for existing records
    $request->free();
    $updated = $bd->updateRecords('reservation_cur c',$where,array('canceled' => 't'),$using); // for updates
    $duplicata = $existing == $updated; // are they duplicatas
  }
  
  verif_transaction();
  
  // récup des infos sur les billets
  $select   = 'e.nom, e.petitnom, m.date, m.txtva, m.id AS manifid, m.description AS desc, e.metaevt,
               (SELECT nom FROM organisme WHERE e.organisme1 = id) AS organisme1,
               (SELECT nom FROM organisme WHERE e.organisme2 = id) AS organisme2,
               (SELECT nom FROM organisme WHERE e.organisme3 = id) AS organisme3,
               s.nom AS sitenom, s.cp, s.ville, s.pays,
               t.description AS tarif, t.prix, mt.prix AS prixspec, t.pass,
               r.plnum, r.transaction AS transac';
  $selectnb = ', count(*) AS nb';
  $groupby  = 'GROUP BY e.nom, e.petitnom, m.date, m.txtva, m.id, e.metaevt,
                        s.nom, s.cp, s.ville, s.pays,
                        t.description, t.prix, mt.prix, t.pass,
                        r.plnum, r.transaction ';
  $where    = '     r.transaction = '.$transac.'
                AND r.id NOT IN ( SELECT resa_preid FROM reservation_cur WHERE NOT canceled )
                '.($tarif ? "AND t.key ILIKE '".pg_escape_string($tarif)."'" : '').'
                '.($manifid ? 'AND r.manifid = '.$manifid : '');
  $orderby  = !$vertical ? ' e.nom, m.date, s.nom, s.ville, t.key' : ' e.nom, t.key, m.date, s.nom, s.ville';
  //$from = 'manifestation m, reservation_pre r, evenement e, site s, tarif AS t LEFT JOIN manifestation_tarifs mt ON mt.tarifid = t.id AND mt.manifestationid = m.id';
  $from = array('reservation_pre r');
  $from[] = 'manifestation m ON m.id = r.manifid';
  $from[] = 'tarif t ON t.id = r.tarifid';
  $from[] = 'evenement e ON e.id = m.evtid';
  $from[] = 'site s ON m.siteid = s.id';
  $from[] = 'manifestation_tarifs mt ON mt.tarifid = t.id AND mt.manifestationid = m.id';
  $from = implode(' LEFT JOIN ',$from);
  $query  = ' SELECT '.$select.'
                     '.($group ? $selectnb : '').'
              FROM   '.$from.'
              WHERE  '.$where.'
              '.($group ? $groupby : '').'
              ORDER BY '.$orderby;
  $request = new bdRequest($bd,$query);
  
  verif_transaction();
  
  $correspondance = array(
    'date'      => 'date',
    'metaevt'   => 'metaevt',
    'sitenom'   => 'sitenom',
    'prix'      => 'prix',
    'tarif'     => 'tarif',
    'evtnom'    => 'nom',
    'createurs' => 'createurs',
    'org'       => 'org',
    'orga'      => 'orga',
    'plnum'     => 'plnum',
    'num'       => 'transac',
    'operateur' => 'userid',
    'nbgroup'   => 'nb',
    'manifid'   => 'manifid',
    'pass'      => 'pass',
    'desc'      => 'desc',
  );
  
  $tickets = new Tickets($group, $vertical);
  
  $last_bill = $bill = array();
  while ( $rec = $request->getRecordNext() )
  {
    $rec['prix']        = round($rec['prixspec'] ? $rec['prixspec'] : $rec['prix'],2);
    $rec['createurs']   = array();
    if ( $rec['organisme1'] )
    $rec['createurs'][] = $rec['organisme1'];
    if ( $rec['organisme2'] )
    $rec['createurs'][] = $rec['organisme2'];
    if ( $rec['organisme3'] )
    $rec['createurs'][] = $rec['organisme3'];
    $rec['createurs']   = implode(', ',$rec['createurs']);
    $rec['userid']      = $user->getId();
    $rec['nom']         = $rec['petitnom'] ? $rec['petitnom'] : $rec['nom'];
    $rec['nb']          = $rec['nb'] ? $rec['nb'] : 1;
    
    // les co-org.
    $query  = ' SELECT o.nom
                FROM manif_organisation mo, organisme o
                WHERE o.id = mo.orgid
                  AND mo.manifid = '.$rec['manifid'];
    $orgs = new bdRequest($bd,$query);
    $rec['orga'] = array($config['ticket']['seller']['nom']);
    while ( $org = $orgs->getRecordNext() )
      $rec['orga'][] = $org['nom'];
    $orgs->free();
    $rec['org'] = implode(', ',$rec['orga']);
    if ( strlen($rec['org']) > 60 )
    {
      $rec['orga'][0] = $rec['orga'][1];
      unset($rec['orga'][1]);
      $rec['org'] = implode(', ',$rec['orga']);
    }
    
    $bill = array();
    foreach ( $correspondance as $key => $value )
      $bill[$key] = $rec[$value];
    if ( $duplicata )
      $bill['info']        = 'duplicata';
    if ( $annulation )
      $bill['info']        = 'annulation';
    $bill['date'] = array($bill['date']);
    
    $arr = array('last' => $last_bill, 'new' => $bill);
    unset($arr['last']['date']);
    unset($arr['new'] ['date']);
    unset($arr['last']['manifid']);
    unset($arr['new'] ['manifid']);
    
    if ( $vertical && $bill['pass'] == 't' && $arr['last'] == $arr['new'] )
    {
      $last_bill['date'] = array_merge($last_bill['date'],$bill['date']);
      $last_bill['manifid'] = $bill['manifid'];
    }
    else
    {
      if ( $last_bill !== array() )
      {
        // permitting tickets to be sorted by thread, not totally grouped (aka "also horizontally")
        for ( $go = true ; $go ; )
        {
          $go = false;
          $dates = array_unique($last_bill['date']);
          $next = array_diff_assoc($last_bill['date'],$dates);
          sort($dates); // retrieving good key orders
          sort($next); // retrieving good key orders
          
          $last_bill['date'] = $dates;
          $tickets->addToContent($last_bill);
          
          $last_bill['date'] = $next;
          if ( count($next) > 0 )
            $go = true;
        }
      }
      $last_bill = $bill;
    }
  }
    
  // finishing printing tickets
  if ( $last_bill )
  for ( $go = true ; $go ; )
  {
    // permitting tickets to be sorted by thread, not totally grouped (aka "also horizontally")
    $go = false;
    $dates = array_unique($last_bill['date']);
    $next = array_diff_assoc($last_bill['date'],$dates);
    sort($dates); // retrieving good key orders
    sort($next); // retrieving good key orders
     
    $last_bill['date'] = $dates;
    $tickets->addToContent($last_bill);
    
    $last_bill['date'] = $next;
    if ( count($next) > 0 )
    $go = true;
  }
  
  $request->free();
  
  // si tout est ok, on met les modifs en base
  $from   = ' reservation_pre p';
  $where  = '    transaction = '.$transac.'
             AND p.id NOT IN ( SELECT resa_preid FROM reservation_cur WHERE NOT canceled ) ';
  if ( $tarif )
  {
    $from   .= ', tarif_manif tm';
    $where  .= "AND p.tarifid = tm.id
                AND p.manifid = tm.manifid
                AND tm.key ILIKE '".$tarif."' ";
  }
  if ( $manifid )
    $where  .= 'AND p.manifid = '.$manifid;
  $query  = ' SELECT p.*
              FROM '.$from.'
              WHERE '.$where;
  $request = new bdRequest($bd,$query);
  while ( $pre = $request->getRecordNext() )
  {
    $cur = array(
      'accountid'   => $user->getId(),
      'resa_preid'  => intval($pre['id']),
    );
    $bd->addRecord('reservation_cur',$cur);
    if ( $salt )
      $_SESSION['tickets'][$salt][] = $bd->getLastSerial('reservation_cur','id');
  }
  
  verif_transaction();

  if ( $config['print']['hard'] )
  {
    function exit_on_error($exit, $err)
    {
      global $bd;
      if ( $exit )
      {
        $bd->endTransaction(false);
        $bd->free();
        echo $err;
        beta_die($err);
      }
      else return true;
    }
    
    exit_on_error($tickets->countTickets() <= 0,254);
    
    $filebase = '/tmp/evt-'.$salt;
    exit_on_error( file_put_contents($filebase.'.html',$tickets->getTicketsHTML()) === false || !file_exists($filebase.'.html') ,3);
    
    // on génére le PDF et on enlève l'HTML
    $cmd = $_SERVER['DOCUMENT_ROOT'].$config['website']['root'].'evt/api/firefox-pdf.bash.php '.$filebase;
    $output = $r = '';
    exec($cmd,$output,$r);
    /*
    if ( intval($r) > 0 )
    {
      shell_exec('Xvfb :10 &> /dev/null &');
      exec($cmd,$output,$r);
    }
    */
    if ( !$config['print']['keep'] ) unlink($filebase.'.html');
    
    // on arrête l'opération en cas de pb
    exit_on_error( !file_exists($filebase.'.pdf') ,4);
    
    shell_exec('cat '.$filebase.'.pdf | /usr/lib/cups/filter/pdftoraster tickets beta tickets 1 1 | /usr/lib/cups/filter/rastertoboca tickets beta bocaprint 1 1 > '.$filebase.'.boca 2> /dev/null');
    if ( !$config['print']['keep'] ) unlink($filebase.'.pdf');
    
    // on arrête l'opération en cas de pb
    $content = file_get_contents($filebase.'.boca');
    exit_on_error( $content === false ,5);
    
    if ( !$config['print']['keep'] ) unlink($filebase.'.boca');
    
    $nav->mimeType('application/vnd.cups-boca');
    echo $content;
  }
  else
  {
    $tickets->printAll();
    $bd->endTransaction();
  }
  
  $bd->free();
?>
