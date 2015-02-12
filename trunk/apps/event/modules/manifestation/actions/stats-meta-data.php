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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $this->getContext()->getConfiguration()->loadHelpers('Number');
    
    $this->json = array(
      'sales'  => array(
        'booked-by-one' => array(),
        'booked-by-one-prepared-by-another' => array(),
        'to-be-paid' => array(),
      ),
    );
    
    // tickets booked & paid by the same person
    $q2 = Doctrine_Query::create()->from('Transaction t')
      ->leftJoin('t.Payments p')
      ->leftJoin('t.Tickets tck')
      ->leftJoin('tck.Version v')
      ->andWhere('tck.id IS NOT NULL AND tck.duplicating IS NULL AND tck.cancelling IS NULL')
      ->andWhere('tck.id NOT IN (SELECT ttck.duplicating FROM Ticket ttck WHERE ttck.duplicating IS NOT NULL)')
      ->andWhere('tck.id NOT IN (SELECT tttck.cancelling FROM Ticket tttck WHERE tttck.cancelling IS NOT NULL)')
      ->andWhere('tck.manifestation_id = ?', $request->getParameter('id', 0))
      ->andWhere('v.version = 1')
      ->andWhere('v.sf_guard_user_id = p.sf_guard_user_id')
      ->select('count(DISTINCT tck.id) AS nb')
      ->andWhere('v.sf_guard_user_id = u.id')
    ;
    $q = Doctrine_Query::create()->from('sfGuardUser u')
      ->select("u.*, ($q2) AS nb")
      ->addParams('where', $q2->getFlattenedParams())
      ->orderBy('u.username');
    $total = 0;
    foreach ( $q->execute() as $user )
    {
      $total += $user->nb;
      $this->json['sales']['booked-by-one'][] = array('user' => (string)$user, 'nb' => $user->nb);
    }
    $this->json['sales']['booked-by-one'][] = array('user' => 'Total', 'nb' => $total);
    
    // tickets booked offline & paid online
    $q2 = Doctrine_Query::create()->from('Transaction t')
      ->leftJoin('t.Version v')
      ->leftJoin('t.Payments p')
      ->leftJoin('t.Tickets tck')
      ->andWhere('tck.id IS NOT NULL AND tck.duplicating IS NULL AND tck.cancelling IS NULL')
      ->andWhere('tck.id NOT IN (SELECT ttck.duplicating FROM Ticket ttck WHERE ttck.duplicating IS NOT NULL)')
      ->andWhere('tck.id NOT IN (SELECT tttck.cancelling FROM Ticket tttck WHERE tttck.cancelling IS NOT NULL)')
      ->andWhere('tck.id IS NOT NULL')
      ->andWhere('tck.manifestation_id = ?', $request->getParameter('id', 0))
      ->andWhere('v.version = 1')
      ->andWhere('v.sf_guard_user_id != p.sf_guard_user_id')
      ->select('count(DISTINCT tck.id) AS nb')
      ->andWhere('p.sf_guard_user_id = u.id')
    ;
    $q = Doctrine_Query::create()->from('sfGuardUser u')
      ->select("u.*, ($q2) AS nb")
      ->addParams('where', $q2->getFlattenedParams())
      ->orderBy('u.username');
    $total = 0;
    foreach ( $q->execute() as $user )
    {
      $total += $user->nb;
      $this->json['sales']['paid-by-one-prepared-by-another'][] = array('user' => (string)$user, 'nb' => $user->nb);
    }
    $this->json['sales']['paid-by-one-prepared-by-another'][] = array('user' => 'Total', 'nb' => $total);
    
    // tickets prepared but still unpaid
    $q2 = Doctrine_Query::create()->from('Transaction t')
      ->leftJoin('t.Order o')
      ->leftJoin('t.Payments p')
      ->andWhere('p.id IS NULL')
      ->andWhere('o.id IS NOT NULL')
      ->leftJoin('t.Tickets tck')
      ->andWhere('tck.id IS NOT NULL AND tck.duplicating IS NULL AND tck.cancelling IS NULL')
      ->andWhere('tck.id NOT IN (SELECT ttck.duplicating FROM Ticket ttck WHERE ttck.duplicating IS NOT NULL)')
      ->andWhere('tck.id NOT IN (SELECT tttck.cancelling FROM Ticket tttck WHERE tttck.cancelling IS NOT NULL)')
      ->andWhere('tck.id IS NOT NULL')
      ->andWhere('tck.manifestation_id = ?', $request->getParameter('id', 0))
      ->select('count(DISTINCT tck.id) AS nb')
      ->andWhere('o.sf_guard_user_id = u.id')
      ->groupBy('o.sf_guard_user_id')
      ->having('sum(tck.value) > 0')
    ;
    $q = Doctrine_Query::create()->from('sfGuardUser u')
      ->select("u.*, ($q2) AS nb")
      ->addParams('where', $q2->getFlattenedParams())
      ->orderBy('u.username');
    $total = 0;
    foreach ( $q->execute() as $user )
    {
      $total += $user->nb;
      $this->json['sales']['to-be-paid'][] = array('user' => (string)$user, 'nb' => is_null($user->nb) ? 0 : $user->nb);
    }
    $this->json['sales']['to-be-paid'][] = array('user' => 'Total', 'nb' => $total);
    
    // tickets prepared, seated, but still unpaid
    $q2->andWhere('tck.seat_id IS NOT NULL');
    $q = Doctrine_Query::create()->from('sfGuardUser u')
      ->select("u.*, ($q2) AS nb")
      ->addParams('where', $q2->getFlattenedParams())
      ->orderBy('u.username');
    $total = 0;
    foreach ( $q->execute() as $user )
    {
      $total += $user->nb;
      $this->json['sales']['seated-to-be-paid'][] = array('user' => (string)$user, 'nb' => is_null($user->nb) ? 0 : $user->nb);
    }
    $this->json['sales']['seated-to-be-paid'][] = array('user' => 'Total', 'nb' => $total);
