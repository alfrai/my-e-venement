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
    // by price's value / tickets
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $users = array();
    foreach ( $criterias['users'] as $user_id )
      $users[] = intval($user_id);
    $q = "SELECT tck.value, count(tck.id) AS nb, sum(tck.value) AS total
          FROM ticket tck
          LEFT JOIN transaction tr ON tr.id = tck.transaction_id
          LEFT JOIN sf_guard_user s2 ON (s2.id = (SELECT tv.sf_guard_user_id FROM transaction_version tv WHERE (tv.id = tr.id AND tv.version = 1))) 
          LEFT JOIN domain d ON s2.id = d.sf_guard_user_id 
          WHERE ".( isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 ? 'manifestation_id IN ('.implode(',',$criterias['manifestations']).')' : '(printed_at IS NOT NULL AND printed_at >= :date0 AND printed_at < :date1 OR integrated_at IS NOT NULL AND integrated_at >= :date0 AND integrated_at < :date1)' )."
            AND tck.id NOT IN (SELECT cancelling FROM ticket WHERE ".(!(isset($criterias['manifestations']) && is_array($criterias['manifestations'])) || count($criterias['manifestations']) == 0 ? 'created_at >= :date0 AND created_at < :date1 AND ' : '')." cancelling IS NOT NULL AND duplicating IS NULL)
            AND tck.cancelling IS NULL
            AND tck.duplicating IS NULL
            ".( isset($criterias['users']) && is_array($criterias['users']) && count($criterias['users']) > 0 ? 'AND tck.sf_guard_user_id IN ('.implode(',',$users).')' : '')."
            ".( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 ? 'AND tck.gauge_id IN (SELECT id FROM gauge g WHERE g.workspace_id IN ('.implode(',',$criterias['workspaces']).'))' : '')."
            ".( !$this->getUser()->hasCredential('tck-ledger-all-users') ? 'AND tck.sf_guard_user_id = '.sfContext::getInstance()->getUser()->getId() : '' )."
            AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL OR tck.cancelling IS NOT NULL)
            ".( ($dom = sfConfig::get('project_internals_users_domain', false)) && $dom != '.' ? " AND (d.name ILIKE '%$dom' OR d.name = '$dom') " : ' ' )."
          GROUP BY tck.value
          ORDER BY tck.value DESC";
    $stmt = $pdo->prepare($q);
    $stmt->execute(isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 ? NULL : array('date0' => $dates[0],'date1' => $dates[1]));
    $this->byValue = $stmt->fetchAll();
    
    // by price's value / products
    if (!( isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0
        || isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0
    ))
    {
      $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
      $users = array();
      foreach ( $criterias['users'] as $user_id )
        $users[] = intval($user_id);
      $q = "SELECT bp.value, count(bp.id) AS nb, sum(bp.value) AS total
            FROM bought_product bp
            LEFT JOIN transaction tr ON tr.id = bp.transaction_id
            LEFT JOIN sf_guard_user s2 ON (s2.id = (SELECT tv.sf_guard_user_id FROM transaction_version tv WHERE (tv.id = tr.id AND tv.version = 1))) 
            LEFT JOIN domain d ON s2.id = d.sf_guard_user_id             
            WHERE bp.integrated_at IS NOT NULL AND bp.integrated_at >= :date0 AND bp.integrated_at < :date1
            ".( isset($criterias['users']) && is_array($criterias['users']) && count($criterias['users']) > 0 ? 'AND bp.sf_guard_user_id IN ('.implode(',',$users).')' : '')."
            ".( !$this->getUser()->hasCredential('tck-ledger-all-users') ? 'AND bp.sf_guard_user_id = '.sfContext::getInstance()->getUser()->getId() : '' )."
            ".( ($dom = sfConfig::get('project_internals_users_domain', false)) && $dom != '.' ? " AND (d.name ILIKE '%$dom' OR d.name = '$dom') " : ' ' )."
          GROUP BY bp.value
          ORDER BY bp.value DESC";
      $stmt = $pdo->prepare($q);
      $stmt->execute(array('date0' => $dates[0],'date1' => $dates[1]));
      foreach ( $stmt->fetchAll() as $val )
      {
        $new = true;
        foreach ( $this->byValue as $i => $preval )
        if ( $preval['value'] == $val['value'] )
        {
          $new = false;
          foreach ( array('nb', 'total') as $key )
            $this->byValue[$i][$key] += $val[$key];
        }
        
        if ( $new )
          $this->byValue[] = $val;
      }
      
      // ordering
      $new = array();
      foreach ( $this->byValue as $value )
        $new[$value['value']] = $value;
      krsort($new);
      $this->byValue = $new;
    }

