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
    // by price's value
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $users = array();
    foreach ( $criterias['users'] as $user_id )
      $users[] = intval($user_id);
    $q = "SELECT value, count(id) AS nb, sum(value) AS total
          FROM ticket
          WHERE ".( is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 ? 'manifestation_id IN ('.implode(',',$criterias['manifestations']).')' : '(printed_at IS NOT NULL AND printed_at >= :date0 AND printed_at < :date1 OR integrated_at IS NOT NULL AND integrated_at >= :date0 AND integrated_at < :date1)' )."
            AND id NOT IN (SELECT cancelling FROM ticket WHERE ".(!is_array($criterias['manifestations']) || count($criterias['manifestations']) == 0 ? 'created_at >= :date0 AND created_at < :date1 AND ' : '')." cancelling IS NOT NULL AND duplicating IS NULL)
            AND cancelling IS NULL
            AND duplicating IS NULL
            ".( is_array($criterias['users']) && count($criterias['users']) > 0 ? 'AND sf_guard_user_id IN ('.implode(',',$users).')' : '')."
            ".( is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 ? 'AND gauge_id IN (SELECT id FROM gauge g WHERE g.workspace_id IN ('.implode(',',$criterias['workspaces']).'))' : '')."
            ".( !$this->getUser()->hasCredential('tck-ledger-all-users') ? 'AND sf_guard_user_id = '.sfContext::getInstance()->getUser()->getId() : '' )."
            AND (printed_at IS NOT NULL OR integrated_at IS NOT NULL OR cancelling IS NOT NULL)
          GROUP BY value
          ORDER BY value DESC";
    //        ".( is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 ? 'manifestation_id IN ('.implode(',',$criterias['manifestations']).')' : '')."
    $stmt = $pdo->prepare($q);
    $stmt->execute(is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 ? NULL : array('date0' =>$dates[0],'date1' => $dates[1]));
    $this->byValue = $stmt->fetchAll();
