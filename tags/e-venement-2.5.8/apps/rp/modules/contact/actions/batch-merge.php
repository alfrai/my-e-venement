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
  $q = Doctrine::getTable('Contact')->createQuery()
    ->whereIn('id',$ids)
    ->orderBy('id');
  $contacts = $q->execute();
  
  $cpt = $contacts->count();
  
  if ( $cpt > 0 )
  {
    foreach ( $contacts as $contact )
    {
      if ( !isset($base_contact) )
        $base_contact = $contact;
      else
      {
        // address
        if ( !$base_contact->address && !$base_contact->postalcode && !$base_contact->city )
        if ( $contact->address && $contact->postalcode && $contact->city && !$contact->npai )
        {
          $base_contact->address = $contact->address;
          $base_contact->postalcode = $contact->postalcode;
          $base_contact->city = $contact->city;
          $base_contact->country = $contact->country;
        }
        
        // email
        if ( !$base_contact->email && $contact->email )
          $base_contact->email = $contact->email;
        
        // password & description
        $base_contact->password = $contact->password;
        $arr = array();
        if ( $base_contact->description ) $arr[] = $base_contact->description;
        if ( $contact->description ) $arr[] = $contact->description;
        $base_contact->description = implode(' ',$arr);
        
        // family contact
        if ( !is_null($contact->family_contact)
          && strtotime($contact->updated_at) > strtotime($base_contact->updated_at) )
          $base_contact->family_contact = $contact->family_contact;
        
        // title
        if ( !$base_contact->title
          && strtotime($contact->updated_at) > strtotime($base_contact->updated_at) )
          $base_contact->title = $contact->title;
        
        // phonenumbers
        foreach ( $contact->Phonenumbers as $phone )
          $base_contact->Phonenumbers[] = $phone;
        
        // membercards
        foreach ( $contact->MemberCards as $mc )
          $base_contact->MemberCards[] = $mc;
        
        // pro + groups
        foreach ( $contact->Professionals as $pro )
          $base_contact->Professionals[] = $pro;
        
        // contact's groups
        foreach ( $contact->ContactGroups as $cgroup )
        {
          $group = new GroupContact;
          $group->group_id = $cgroup->group_id;
          
          $addit = true;
          foreach ( $base_contact->ContactGroups as $gp )
          if ( $gp->group_id == $group->group_id )
            $addit = false;
          
          if ( $addit )
            $base_contact->ContactGroups[] = $group;
        }
        
        // contact's emailings
        foreach ( $contact->Emails as $email )
          $base_contact->Emails[] = $email;
        
        // locations
        foreach ( $contact->Locations as $location )
          $base_contact->Locations[] = $location;
        
        // transactions
        foreach ( $contact->Transactions as $transaction )
          $base_contact->Transactions[] = $transaction;
        
        // YOB
        foreach ( $contact->YOBs as $YOB )
          $base_contact->YOBs[] = $YOB;
        
        // Relationships
        foreach ( $contact->Relationships as $relationship )
          $base_contact->Relationships[] = $relationship;
        
        $base_contact->save();
        $contact->delete();
      }
    }
    $this->getUser()->setFlash('notice',__('%%nb%% contacts properly merged into one',array('%%nb%%' => $cpt)));
  }
  else
    $this->getUser()->setFlash('notice',__('You have to select more than one contact to be able to merge something'));
