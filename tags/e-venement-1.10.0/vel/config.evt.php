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
*    Copyright (c) 2006-2008 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
  // the hook
  $config['evt']['callback_transaction'][] = 'evt_vel_bank';
  
  // the function
  function evt_vel_bank($tid)
  {
    global $bd;
    
    $query = ' SELECT bp.transaction_id
               FROM vel.bank_payment bp
               LEFT JOIN paiement p ON bp.paiementid = p.id
               WHERE p.transaction = '.intval($tid).'
               ORDER BY bp.transaction_id';
    $request = new bdRequest($bd,$query);
    $bank = array();
    while ( $rec = $request->getRecordNext() )
      $bank[] = htmlsecure($rec['transaction_id']);
    $request->free();
    
    $span = '<span title="transaction_id de la banque" class="bank">';
    if ( count($bank) > 0 )
      echo '<span class="separator">/ </span>'.$span.'~'.implode('~</span>, '.$span,$bank).'</span>';
  }
?>
