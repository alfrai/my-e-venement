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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
  /**
    * usage: php all.php table1 [table2 [table3 ...]]
    * where: tableX is a table where import data to
    *
    **/

  require 'config.php';
  require 'functions.php';
  includeClass('bd');
  includeClass('bdRequest');
  
  $bd2    = new bd ( $config["database"]["name"],
                     $config["database"]["server"],
                     $config["database"]["port"],
                     $config["database"]["user"],
                     $config["database"]["passwd"] );
  $bd     = new bd ( $config["database2"]["name"],
                     $config["database2"]["server"],
                     $config["database2"]["port"],
                     $config["database2"]["user"],
                     $config["database2"]["passwd"] );
  
  $do = $_SERVER['argv'];
  unset($do[0]);
  
  $tables = array();
  
  // contact
  $from_table = 'personne';
  $to_table = 'contact';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $tables[] = $to_table;
    echo $to_table.' ';
    $conversion = array(
      'id'          => 'id',
      'prenom'      => 'firstname',
      'nom'         => 'name',
      'titre'       => 'title',
      'creation'    => 'created_at',
      'modification'=> 'updated_at',
      'description' => 'description',
      //'password'    => 'password',
      'adresse'     => 'address',
      'cp'          => 'postalcode',
      'ville'       => 'city',
      'pays'        => 'country',
      'email'       => 'email',
      'npai'        => 'npai',
    );
    $conversion = array_flip($conversion); // for lazzy workers
    $conversion['slug'] = NULL;
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  
  // organism_category
  $from_table = 'org_categorie';
  $to_table = 'organism_category';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $tables[] = $to_table;
    echo $to_table.' ';
    $conversion = array(
      'id'          => 'id',
      'name'        => 'libelle',
      'created_at'  => NULL,
      'updated_at'  => NULL,
    );
    $conversion['slug'] = NULL;
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  
  // organism
  $from_table = 'organisme';
  $to_table = 'organism';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'id'          => 'id',
      'nom'         => 'name',
      'creation'    => 'created_at',
      'modification'=> 'updated_at',
      'description' => 'description',
      'adresse'     => 'address',
      'cp'          => 'postalcode',
      'ville'       => 'city',
      'pays'        => 'country',
      'email'       => 'email',
      'npai'        => 'npai',
      'url'         => 'url',
      'categorie'   => 'organism_category_id',
    );
    $conversion = array_flip($conversion); // for lazzy workers
    $conversion['slug'] = NULL;
    $tables[] = $to_table;
    echo $to_table.' ';
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  
  // telephones contact
  $from_table = 'telephone';
  $to_table = 'contact_phonenumber';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'name' => 'type',
      'number' => 'numero',
      'contact_id' => 'entiteid',
      'created_at'  => NULL,
      'updated_at'  => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  
  // telephones organisme
  $from_table = 'telephone';
  $to_table = 'organism_phonenumber';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'name' => 'type',
      'number' => 'numero',
      'organism_id' => 'entiteid',
      'created_at'  => NULL,
      'updated_at'  => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  
  // YOB
  $from_table = 'child';
  $to_table = 'y_o_b';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'year' => 'birth',
      'contact_id' => 'personneid',
      'created_at'  => NULL,
      'updated_at'  => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  
  // professional_type
  $to_table = 'professional_type';
  $from_table = 'fonction';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'id'          => 'id',
      'name'        => 'libelle',
      'created_at'  => NULL,
      'updated_at'  => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  
  // professional
  $to_table = 'professional';
  $from_table = 'org_personne';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'id'            => 'id',
      'organism_id'   => 'organismeid',
      'contact_id'    => 'personneid',
      'professional_type_id' => 'type',
      'name'          => 'fonction',
      'contact_number'=> 'telephone',
      'contact_email' => 'email',
      'department'    => 'service',
      'description'   => 'description',
      'created_at'    => NULL,
      'updated_at'    => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  function no_sf_guard_user_id($rec)
  {
    global $bd, $sf_guard_user_id;
    
    if ( isset($sf_guard_user_id) )
      return $sf_guard_user_id;
    
    $request = new bdRequest($bd,"SELECT min(id) id FROM sf_guard_user");
    if ( $request->countRecords() > 0 )
    {
      $sf_guard_user_id = $request->getRecord('id');
      return $request->getRecord('id');
    }
    
    return NULL;
  }
  
  // users
  $to_table = 'sf_guard_user';
  $from_table = 'account';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'id'            => 'id',
      'first_name'    => 'name',
      'email_address' => array('email','name',''),
      'username'      => 'login',
      'active'        => 'is_active',
      'created_at'    => NULL,
      'updated_at'    => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  
  // group_table
  $to_table = 'group_table';
  $from_table = 'groupe';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'id'            => 'id',
      'name'          => 'nom',
      'sf_guard_user_id' => 'createur',
      'created_at'    => 'creation',
      'updated_at'    => 'modification',
      'description'   => 'description',
      'slug'          => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  
  // group_contact
  $to_table = 'group_contact';
  $from_table = 'groupe_personnes';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'group_id'      => 'groupid',
      'contact_id'    => 'personneid',
      'information'   => 'info',
      'created_at'    => NULL,
      'updated_at'    => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  
  // group_professional
  $to_table = 'group_professional';
  $from_table = 'groupe_fonctions';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'group_id'      => 'groupid',
      'professional_id'    => 'fonctionid',
      'information'   => 'info',
      'created_at'    => NULL,
      'updated_at'    => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  
  // model_type
  $to_table = 'model_type';
  $from_table = 'str_model';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'name'          => 'str',
      'type'          => 'usage',
      'created_at'    => NULL,
      'updated_at'    => NULL,
      'slug'          => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,true,"usage != 'metaevt'","str, case when usage = 'teltype' then 'phone' when usage = 'titretype' then 'title' else 'default' end AS usage");
    print_r($cpt);
  }
  
  // option
  $to_table = 'option_table';
  $from_table = 'options';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'name'          => 'key',
      'type'          => 'type',
      'value'         => 'value',
      'created_at'    => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,true,"substring(key,0,8) = 'labels.'","substring(key,8) AS key, value, 'labels' AS type");
    print_r($cpt);
  }
  
  // emails
  $to_table = 'email';
  $from_table = 'email';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'sf_guard_user_id'    => 'accountid',
      'field_from'          => 'from',
      'field_to'            => 'to',
      //'field_cc'            => 'cc',
      'field_bcc'           => 'bcc',
      'field_subject'       => 'subject',
      'content'             => 'content',
      'created_at'          => NULL,
      'updated_at'          => 'date',
      'sent'                => 'sent'
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  
  // logs
  $to_table = 'authentication';
  $from_table = 'login';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'sf_guard_user_id'    => 'accountid',
      'description'         => 'triedname',
      'ip_address'          => 'ipaddress',
      'success'             => 'success',
      'created_at'          => 'date',
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  
  // vat
  $to_table = 'vat';
  $from_table = "(select * from (select txtva||'%' AS txtva, txtva/100 AS value from billeterie.evt_categorie group by txtva) as truc) AS vat";
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'name' => 'txtva',
      'value' => 'value',
      'created_at' => NULL,
      'updated_at' => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  function find_vat_id($rec)
  {
    global $bd, $vat_ids;
    
    if ( isset($vat_ids) && isset($vat_ids[$rec['txtva']]) )
      return $vat_ids[$rec['txtva']];
    
    $request = new bdRequest($bd,"SELECT * FROM vat WHERE name = '".pg_escape_string($rec['txtva'])."%'");
    if ( $request->countRecords() > 0 )
    {
      $vat_ids[$request->getRecord('name')] = $request->getRecord('id');
      return $request->getRecord('id');
    }
    
    return NULL;
  }
  
  // event_category
  $to_table = 'event_category';
  $from_table = 'billeterie.evt_categorie';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'id' => 'id',
      'name' => 'libelle',
      'vat_id' => '_find_vat_id',
      'created_at' => NULL,
      'updated_at' => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  
  // event
  $to_table = 'event';
  $from_table = 'billeterie.evenement';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $metaevtids = array();
    function add_event_company($event_id,$organism_id)
    {
      global $bd;
      
      if ( !$organism_id )
        return false;
      
      if ( !$bd->addRecord('event_company',array('event_id' => $event_id, 'organism_id' => $organism_id)) )
        die($bd->getLastRequest());
    }
    function add_event_company1($event_id,$rec)
    { add_event_company($event_id,$rec['organisme1']); }
    function add_event_company2($event_id,$rec)
    { add_event_company($event_id,$rec['organisme2']); }
    function add_event_company3($event_id,$rec)
    { add_event_company($event_id,$rec['organisme3']); }
    function metaevt($rec)
    {
      global $metaevtids, $bd;
      $cpt = 0;
      
      if ( !isset($metaevtids[$rec['metaevt']]) || !$metaevtids[$rec['metaevt']] )
      {
        $request = new bdRequest($bd,"SELECT * FROM meta_event WHERE name = '".pg_escape_string($rec['metaevt'])."'");
        if ( $request->countRecords() > 0 )
        {
          $metaevtids[$request->getRecord('name')] = $request->getRecord('id');
          return $request->getRecord('id');
        }
        else if ( $rec['metaevt'] )
        {
          if ( $bd->addRecord('meta_event',$arr = array(
            'name' => $rec['metaevt'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
          )) === false )
          {
            var_dump($arr);
            die('pas cool metaevt');
          }
          $metaevtids[$rec['metaevt']] = $bd->getLastSerial('meta_event','id');
          return $metaevtids[$rec['metaevt']] ? $metaevtids[$rec['metaevt']] : NULL;
        }
        else
          return NULL;
      }
      else
        return $metaevtids[$rec['metaevt']] ? $metaevtids[$rec['metaevt']] : NULL;
    }
    
    $conversion = array(
      'id' => 'id',
      'name' => 'nom',
      'short_name' => 'petitnom',
      '_add_event_company1' => 'organisme1',
      '_add_event_company2' => 'organisme2',
      '_add_event_company3' => 'organisme3',
      'meta_event_id' => '_metaevt',
      'event_category_id' => 'categorie',
      'event_category_description' => 'typedesc',
      'staging' => 'mscene',
      'staging_label' => 'mscene_lbl',
      'writer' => 'textede',
      'writer_label' => 'textede_lbl',
      'duration' => 'duree',
      'age_min' => 'ages_min',
      'age_max' => 'ages_max',
      'updated_at' => 'modification',
      'created_at' => 'creation',
      'slug' => NULL,
      'extradesc' => 'extradesc',
      'extraspec' => 'extraspec',
      'web_price' => 'tarifweb',
      'web_price_group' => 'tarifwebgroup',
      'image_url' => 'imageurl',
      'sf_guard_user_id' => '_no_sf_guard_user_id',
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,true,NULL,'*, ages[1] AS ages_min, ages[2] AS ages_max');
    print_r($metaevtids);
    print_r($cpt);
  }
  
  // workspace
  $to_table = 'workspace';
  $from_table = 'billeterie.space';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $bd2->addRecord($from_table,array('name' => 'default space'));
    $conversion = array(
      'id' => 'id',
      'name' => 'name',
      'created_at' => NULL,
      'updated_at' => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  
  // color
  $to_table = 'color';
  $from_table = 'billeterie.color';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'id' => 'id',
      'name' => 'libelle',
      'color' => 'color',
      'created_at' => NULL,
      'updated_at' => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  
  // price
  $to_table = 'price';
  $from_table = 'billeterie.tarif';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'id' => 'id',
      'name' => 'key',
      'description' => 'description',
      'online' => 'vel',
      'value' => 'prix',
      'created_at' => 'date',
      'updated_at' => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false,'NOT contingeant AND NOT desact AND id = (SELECT max(id) AS id FROM billeterie.tarif t WHERE t.key = tarif.key)');
    print_r($cpt);
  }
  
  // location
  $to_table = 'location';
  $from_table = 'billeterie.site';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'id' => 'id',
      'name' => 'nom',
      'address' => 'adresse',
      'postalcode' => 'cp',
      'city' => 'ville',
      'country' => 'pays',
      'contact_id' => 'regisseur',
      'organism_id' => 'organisme',
      'gauge_min' => 'jauge_min',
      'gauge_max' => 'jauge_max',
      'created_at' => NULL,
      'updated_at' => NULL,
      'slug' => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  
  // manifestation
  $to_table = 'manifestation';
  $from_table = 'billeterie.manifestation';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    function reservation_begins_at($rec)
    {
      return $rec['date'];
    }
    function reservation_ends_at($rec)
    {
      return date('Y-m-d H:i:s', $rec['seconds'] + strtotime($rec['date']));
    }
    function manif_duree($rec)
    {
      return strtotime('+'.$rec['duree']) - strtotime('now');
    }
    
    $conversion = array(
      'id' => 'id',
      'event_id' => 'evtid',
      'location_id' => 'siteid',
      'color_id' => 'colorid',
      'happens_at' => 'date',
      'duration'   => '_manif_duree',
      'description' => 'description',
      'vat_id' => '_find_vat_id',
      //'seated' => 'plnum',
      'created_at' => NULL,
      'updated_at' => NULL,
      'sf_guard_user_id'      => '_no_sf_guard_user_id',
      'reservation_begins_at' => '_reservation_begins_at',
      'reservation_ends_at'   => '_reservation_ends_at',
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,true,'','*, extract(epoch from duree) AS seconds');
    print_r($cpt);
  }
  
  // price_manifestation
  $to_table = 'price_manifestation';
  $from_table = 'billeterie.manifestation_tarifs';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'manifestation_id' => 'manifestationid',
      'price_id' => 'last_tarifid',
      'value' => 'prix',
      'created_at' => NULL,
      'updated_at' => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,true,
      'NOT (select desact from billeterie.tarif where id = (select max(id) from billeterie.tarif where key = (select t.key from billeterie.tarif t where t.id = manifestation_tarifs.tarifid)))',
      '*, (select max(id) from billeterie.tarif where key = (select t.key from billeterie.tarif t where t.id = manifestation_tarifs.tarifid)) AS last_tarifid');
    print_r($cpt);
  }
  
  // gauge
  $to_table = 'gauge';
  $from_table = 'billeterie.space_manifestation';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'manifestation_id' => 'manifid',
      'workspace_id' => 'spaceid',
      'value' => 'jauge',
      'online' => 'online',
      'created_at' => NULL,
      'updated_at' => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,true,'','*, (select vel from billeterie.manifestation m where manifid = m.id) AS online');
    print_r($cpt);
  }
  $from_table = 'billeterie.manifestation';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    function gauge_spaceid($rec)
    {
      global $default_wsid, $bd;
      if ( isset($default_wsid) )
        return $default_wsid;
      
      $request = new bdRequest($bd,"SELECT min(id) id FROM workspace");
      return $default_wsid = $request->getRecord('id');
    }
    
    $conversion = array(
      'manifestation_id'  => 'id',
      'workspace_id'      => '_gauge_spaceid',
      'value'             => 'jauge',
      'online'            => 'vel',
      'created_at'        => NULL,
      'updated_at'        => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,true,'',"*, CASE WHEN jauge IS NULL THEN 0 ELSE jauge END AS jauge");
    print_r($cpt);
  }
  
  print_r($tables);
  
  $bd->free();
  $bd2->free();
?>
