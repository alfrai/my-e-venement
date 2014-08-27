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
    // filtering criterias
    $this->options = $criterias = $this->formatCriterias($request);
    $dates = $criterias['dates'];
    if ( !isset($criterias['users']) )
      $criterias['users'] = array();
    
    // redirect to avoid POST re-sending
    if ( $request->getParameter($this->form->getName(),false) )
      $this->redirect('ledger/both');
    
    require(dirname(__FILE__).'/both-payment.php');
    require(dirname(__FILE__).'/both-price.php');
    require(dirname(__FILE__).'/both-ticket-value.php');
    require(dirname(__FILE__).'/both-taxes.php');
    require(dirname(__FILE__).'/both-user.php');
    require(dirname(__FILE__).'/both-manifestations.php');
    require(dirname(__FILE__).'/both-workspaces.php');
