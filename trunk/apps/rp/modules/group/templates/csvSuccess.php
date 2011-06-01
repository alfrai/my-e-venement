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
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  $outstream = fopen($outstream, 'w');
  
  // setting variables for all partials
  $vars = array(
    'options',
    'delimiter',
    'enclosure',
    'outstream',
  );
  foreach ( $vars as $key => $value )
  {
    $vars[$value] = $$value;
    unset($vars[$key]);
  }
  
  // header
  include_partial('global/csv_headers',$vars);
  
  // all personal members
  foreach ( $group->Contacts as $contact )
  {
    $line = array(
      $contact->title,
      $contact->name,
      $contact->firstname,
      $contact->address,
      $contact->postalcode,
      $contact->city,
      $contact->country,
      $contact->npai,
      $contact->email,
      $contact->Phonenumbers[0]->name,
      $contact->Phonenumbers[0]->number,
      $contact->description,
    );
    
    include_partial('global/csv_line',array_merge(array('line' => $line),$vars));
  }
  
  foreach ( $group->Professionals as $professional )
  {
    $contact = $professional->Contact;
    
    $line = array(
      $contact->title,
      $contact->name,
      $contact->firstname,
      $contact->address,
      $contact->postalcode,
      $contact->city,
      $contact->country,
      $contact->npai,
      $contact->email,
      $contact->Phonenumbers[0]->name,
      $contact->Phonenumbers[0]->number,
      $contact->description,
      $professional['Organism']['Category'],
      $professional['Organism'],
      $professional['department'],
      $professional['contact_number'],
      $professional['contact_email'],
      $professional['ProfessionalType'],
      $professional,
      $professional['Organism']['address'],
      $professional['Organism']['postalcode'],
      $professional['Organism']['city'],
      $professional['Organism']['country'],
      $professional['Organism']['email'],
      $professional['Organism']['url'],
      $professional['Organism']['description'],
      $professional['Organism']['npai'],
    );

    include_partial('global/csv_line',array_merge(array('line' => $line),$vars));
  }
  
  fclose($outstream);
