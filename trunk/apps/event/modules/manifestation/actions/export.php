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
*    Foundation, Inc., 5'.$rank.' Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $this->getContext()->getConfiguration()->loadHelpers(array('Date','CrossAppLink'));
    $manifestation = $this->getRoute()->getObject();
    
    $q = new Doctrine_Query;
    $q->from('Contact c')
      ->leftJoin('c.Transactions t')
      ->leftJoin('t.Professional tp')
      ->leftJoin('t.Tickets tck')
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('c.Professionals cp ON c.id = cp.contact_id AND (cp.id = tp.id OR cp.id IS NULL AND tp.id IS NULL)')
      ->select('c.*, cp.*')
      ->andWhere('m.id = ?',$manifestation->id)
      ->andWhere('tck.cancelling IS NULL')
      ->andWhere('tck.id NOT IN (SELECT tck2.cancelling FROM Ticket tck2 WHERE tck2.cancelling IS NOT NULL)');
    
    switch ( $type = $request->getParameter('status') ) {
    case 'asked':
      $q->leftJoin('t.Order o')
        ->andWhere('o.id IS NULL')
        ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL');
      break;
    case 'ordered':
      $q->leftJoin('t.Order o')
        ->andWhere('o.id IS NOT NULL')
        ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL');
      break;
    default:
      $q->andWhere('(tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL)');
      break;
    }

    $contacts = $q->execute();
    
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    $i18n = array(
      'asked'   => __('Asked tickets'),
      'ordered' => __('Ordered tickets'),
      'printed' => __('Printed tickets'),
    );
    
    $group = new Group;
    $group->name = $manifestation.' / '.format_datetime(date('Y-m-d H:i:s')).' ('.strtolower($i18n[$type]).')';
    $group->sf_guard_user_id = $this->getUser()->getId();
    
    foreach ( $contacts as $contact )
    if ( $contact->Professionals->count() > 0 )
    foreach ( $contact->Professionals as $professional )
      $group->Professionals[] = $professional;
    else
      $group->Contacts[] = $contact;
    
    $group->save();
    $this->redirect(cross_app_url_for('rp','group/show?id='.$group->id));
    return sfView::NONE;
