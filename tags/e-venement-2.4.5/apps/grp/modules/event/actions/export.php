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
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $this->getContext()->getConfiguration()->loadHelpers(array(
      'CrossAppLink',
      'I18N',
    ));
    $event = $this->getRoute()->getObject();
    
    $request->setAttribute('type','accepted');
    $q = Doctrine::getTable('Professional')->createQuery('p')
      ->leftJoin('p.ContactEntries ce')
      ->leftJoin('ce.Entry e')
      ->leftJoin('e.ManifestationEntries me')
      ->leftJoin('me.Manifestation m')
      ->leftJoin('ce.Entries ee ON ee.manifestation_entry_id = me.id AND ee.contact_entry_id = ce.id')
      ->andWhere('ee.accepted = TRUE')
      ->andWhere('m.event_id = ?',$event->id);
    if ( ($meid = intval($request->getParameter('manifestation_id'))) > 0 )
      $q->andWhere('me.id = ?',$meid);
    $pros = $q->execute();
    
    if ( $pros->count() > 0 )
    {
      $grp = new Group;
      $grp->name = __('Groups: %%name%%, created on %%date%%',array(
        '%%name%%' => $meid > 0 ? $pros[0]->ContactEntries[0]->Entry->ManifestationEntries[0]->Manifestation : $event->name,
        '%%date%%' => date('Y-m-d H:i:s'),
      ));
      $grp->sf_guard_user_id = $this->getUser()->getId();
      foreach ( $pros as $pro )
      {
        $grp->Professionals[] = $pro;
      }
      $grp->save();
      
      $this->getUser()->setFlash('notice','The item was created successfully.');
      $this->redirect(cross_app_url_for('rp','group/show?id='.$grp->id));
    }
    else
    {
      $this->getUser()->setFlash('notice',__('The group was not created successfully : there is no member to add.'));
      $this->redirect('event/edit?id='.$event->id);
    }
    
