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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2011 Ayoub HIDRI <ayoub.hidri AT gmail.com>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $q = $this->buildQuery();
    $a = $q->getRootAlias();
    $q->select   ("$a.id")
      ->addSelect('transac.professional_id AS professional_id');
    if ( !$q->contains("LEFT JOIN $a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)") )
      $q->leftJoin("$a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)");
    $records = $q->fetchArray();
    
    if ( $q->count() > 0 )
    {
      $group = new Group();
      if ( $this->getUser() instanceof sfGuardSecurityUser )
        $group->sf_guard_user_id = $this->getUser()->getId();
      $group->name = __('Search group').' - '.date('Y-m-d H:i:s');
      $group->save();
      
      foreach ( $records as $record )
      {
        // contact
        if ( !$record['professional_id'] )
        {
          $member = new GroupContact();
          $member->contact_id = $record['id'];
        }
        else
        {
          $member = new GroupProfessional();
          $member->professional_id = $record['professional_id'];
        }
        
        $member->group_id   = $group->id;
        $member->save();
      }
    }
    
    $this->redirect(url_for('group/show?id='.$group->id));
    $this->useClassicTemplateDir(true);
    return sfView::NONE;
