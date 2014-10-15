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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  $csv = array_map('str_getcsv', file($_FILES['rp-import']['tmp_name']));
  unset($csv[0]);
  $matches = array(
    'id',
    'etablissement1',
    'prenom',
    'nom',
    'adresse1',
    'adresse2',
    'cp',
    'ville',
    'groupe',
    'telephone',
    'mail',
  );
  $this->logs = array('Organism' => array(), 'Contact' => array(), 'Professional' => array());
  
  $errors = $cats = $grps = $orgs = array();
  $cpt = 0;
  foreach ( $csv as $entry ) { try
  {
    foreach ( $matches as $i => $field )
      $entry[$field] = isset($entry[$i]) ? $entry[$i] : '';
    
    $cpt++;
    
    // organism
    $org = NULL;
    if ( $entry['etablissement1'] && isset($orgs[$entry['etablissement1']]) )
      $org = $orgs[$entry['etablissement1']];
    elseif ( $entry['etablissement1'] )
    {
      $org = new Organism;
      $org->name = $entry['etablissement1'];
      if ( $entry['adresse1'] )
        $org->address = $entry['adresse1'].($entry['adresse2'] ? "\n".$entry['adresse2'] : '');
      $org->postalcode = $entry['cp'];
      $org->city = $entry['ville'];
      
      // organism_category
      if ( $entry['groupe'] )
      {
        if ( !isset($cats[$entry['groupe']]) )
        {
          $cat = new OrganismCategory;
          $cat->name = $entry['groupe'];
          $cat->save();
          $cats[$cat->name] = $cat;
        }
        $org->Category = $cats[$entry['groupe']];
      }
      
      $org->description = $entry['id'];
      $org->save();
      $this->logs['Organism'][] = str_pad($entry['id'], 4, '0', STR_PAD_LEFT).' '.$org;
      $orgs[$org->name] = $org;
    }
    
    // contact
    if ( $entry['nom'] )
    {
      $contact = new Contact;
      $contact->name = $entry['nom'];
      $contact->firstname = $entry['prenom'];
      $contact->description = $entry['id'];
      
      if ( $org )
      {
        $pro = new Professional;
        $pro->Contact = $contact;
        $pro->Organism = $org;
        $pro->description = $entry['id'];
        if ( $entry['telephone'] )
          $pro->contact_number = $entry['telephone'];
        $pro->save();
        $this->logs['Professional'][] = str_pad($entry['id'], 4, '0', STR_PAD_LEFT)." $org: $contact";
      }
      else
      {
        if ( $entry['telephone'] )
        {
          $tel = new ContactPhonenumber;
          $tel->number = $entry['telephone'];
          $contact->Phonenumbers[] = $tel;
        }
        
        // personal address
        $contact->address = $entry['addresse1'];
        if ( $entry['addresse2'] )
          $contact->address = $entry['addresse1']."\n".$entry['address2'];
        $contact->postalcode = $entry['cp'];
        $contact->city = $entry['ville'];
        $contact->email = $entry['mail'];
        
        // personal group
        if ( $entry['groupe'] )
        {
          if ( !isset($grps[$entry['groupe']]) )
          {
            $grp = new Group;
            $grp->name = $entry['groupe'];
            $grp->save();
            $grps[$grp->name] = $grp;
          }
          $contact->Groups[] = $grps[$entry['groupe']];
        }
      
        $contact->save();
        $this->logs['Contact'][] = str_pad($entry['id'], 4, '0', STR_PAD_LEFT)." $contact";
      }
    }
    elseif ( $org && $entry['telephone'] )
    {
      $tel = new OrganismPhonenumber;
      $tel->name = 'Standard';
      $tel->number = $entry['telephone'];
      $org->Phonenumbers[] = $tel;
      $org->save();
    }
  } catch ( Exception $e ) {
    error_log(str_pad($entry['id'], 4, '0', STR_PAD_LEFT).' '.$org.' '.$e->getMessage());
  } }
  
  if ( count($errors) > 0 )
    $this->getUser()->setFlash('error', count($errors).' errors.');
  $notices = array();
  foreach ( $this->logs as $part => $msgs )
    $notices[] = $part.': '.count($msgs);
  $this->getUser()->setFlash('notice', implode(' ; ', $notices));
  $this->redirect('contact/prepareImport');
?>
