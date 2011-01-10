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
  
  while ( true )
  {
    $contacts = $pager->getResults();
    
    // personal information
    foreach ( $contacts as $contact )
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
      
      // do we need to show this personal relation ?
      $personal = true;
      if ( count($groups_list) > 0 )
      {
        $personal = false;
        foreach ( $contact->Groups as $group )
        if ( in_array($group->id,$groups_list) )
        {
          $personal = true;
          break;
        }
      }
      
      if ( !$options['pro_only'] && $personal )
        include_partial('global/csv_line',array_merge(array('line' => $line),$vars));
      
      $i = count($line);
      
      // professional informations
      if ( !$options['nopro'] )
      foreach ( $contact->Professionals as $professional )
      {
        // do we need to show this professional relation ?
        $professional = true;
        if ( count($groups_list) > 0 )
        {
          $professional = false;
          foreach ( $contact->Groups as $group )
          if ( in_array($group->id,$groups_list) )
          {
            $professional = true;
            break;
          }
        }
        if ( $professional )
          continue;
        
        $j = $i;
        $line[$j++] = $professional['Organism']['Category'];
        $line[$j++] = $professional['Organism'];
        $line[$j++] = $professional['department'];
        $line[$j++] = $professional['contact_number'];
        $line[$j++] = $professional['contact_email'];
        $line[$j++] = $professional['ProfessionalType'];
        $line[$j++] = $professional;
        $line[$j++] = $professional['Organism']['address'];
        $line[$j++] = $professional['Organism']['postalcode'];
        $line[$j++] = $professional['Organism']['city'];
        $line[$j++] = $professional['Organism']['country'];
        $line[$j++] = $professional['Organism']['email'];
        $line[$j++] = $professional['Organism']['url'];
        $line[$j++] = $professional['Organism']['description'];
        $line[$j++] = $professional['Organism']['npai'];

        include_partial('global/csv_line',array_merge(array('line' => $line),$vars));
      }
    }
    
    if ( $pager->getPage() + 1 > $pager->getLastPage() )
      break;
    $pager->setPage($pager->getPage() + 1);
    $pager->init();
  }
  
  fclose($outstream);
