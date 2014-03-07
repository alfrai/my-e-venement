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
*    Copyright (c) 2011 Ayoub HIDRI <ayoub.hidri AT gmail.com>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

  $this->getContext()->getConfiguration()->loadHelpers('I18N');
  
  $ids = $request->getParameter('ids');
  $q = Doctrine::getTable('Organism')->createQuery()
    ->whereIn('id',$ids)
    ->orderBy('id');
  $organisms = $q->execute();
  
  $cpt = $organisms->count();
  
  if ( $cpt > 0 )
  {
    foreach ( $organisms as $organism )
    {
      if ( !isset($base_organism) )
        $base_organism = $organism;
      else
      {
        $recent = strtotime($contact->updated_at) > strtotime($base_contact->updated_at);
        
        // personal informations
        if ( $recent )
          $base_organism->name       = $organism->name;

        // address
        if ( !$base_organism->address && !$base_organism->postalcode && !$base_organism->city
          || $recent )
        if ( $organism->address && $organism->postalcode && $organism->city && !$organism->npai )
        {
          $base_organism->address = $organism->address;
          $base_organism->postalcode = $organism->postalcode;
          $base_organism->city = $organism->city;
          $base_organism->country = $organism->country;
        }
        
        // email
        if ( !$base_organism->email && $organism->email
          || $organism->email && $recent )
          $base_organism->email = $organism->email;
        
        // password & description
        $arr = array();
        if ( $base_organism->description ) $arr[] = $base_organism->description;
        if ( $organism->description ) $arr[] = $organism->description;
        $base_organism->description = implode("\n",$arr);
        
        // contact's groups
        foreach ( $organism->OrganismGroups as $cgroup )
        {
          $group = new GroupOrganism;
          $group->group_id = $cgroup->group_id;
          
          $addit = true;
          foreach ( $base_organism->OrganismGroups as $gp )
          if ( $gp->group_id == $group->group_id )
            $addit = false;
          
          if ( $addit )
            $base_organism->OrganismGroups[] = $group;
        }
        
        foreach (array(
          'Events',
          'EventCompanies',
          'Locations',
          'ManifestationOrganizers',
          'Emails',
          'Professionals',
          'Phonenumbers',
        ) as $elts )
        foreach ( $organism->$elts as $obj )
        {
          $collection = $base_organism->{$elts}[] = $obj;
          if ( isset($obj->organism_id) )
          {
            $obj->organism_id = $base_organism->id
            $obj->save();
          }
        }
        
        // for multiple merges
        if ( $recent )
          $base_contact->updated_at = $contact->updated_at;
        
        $organism->delete();
      }
    }
    if ( $base_organism )
      $base_organism->save();
    
    $this->getUser()->setFlash('notice',__('%%nb%% organisms properly merged into one',array('%%nb%%' => $cpt)));
  }
  else
    $this->getUser()->setFlash('notice',__('You have to select more than one organism to be able to merge something'));
