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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $charset = sfConfig::get('software_internals_charset');
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q',''));
    
    if ( !$request->hasParameter('limit') )
      $request->setParameter('limit',50);
    
    $past = sfConfig::get('app_control_past') ? sfConfig::get('app_control_past') : '6 hours';
    $future = sfConfig::get('app_control_future') ? sfConfig::get('app_control_future') : '1 day';
    $q = Doctrine::getTable('Checkpoint')
      ->createQuery('c')
      ->select('c.*')
      ->limit($request->getParameter('limit'))
      ->leftJoin('c.Event e')
      ->leftJoin('e.Manifestations m')
      ->andWhere('m.happens_at < ?',date('Y-m-d H:i',strtotime('now + '.$future)))
      ->andWhere('m.happens_at >= ?',date('Y-m-d H:i',strtotime('now - '.$past)))
    ;
    
    $this->checkpoints = array();
    foreach ( $q->execute() as $group )
      $this->checkpoints[$group->id] = (string)$group;
    
    if (!( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') ))
      return 'Json';
    return 'Success';
