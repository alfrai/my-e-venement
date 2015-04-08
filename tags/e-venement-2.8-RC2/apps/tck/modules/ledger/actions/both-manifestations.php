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
    // get all selected manifestations
    $this->manifestations = false;
    if ( count($criterias['manifestations']) > 0 )
    {
      $q = Doctrine::getTable('Manifestation')->createQuery('m')
        ->andWhereIn('m.id',$criterias['manifestations']);
      $this->manifestations = $q->execute();
      
      $q = Doctrine::getTable('Gauge')->createQuery('g')
        ->leftJoin('g.Manifestation m')
        ->leftJoin('m.Event e')
        ->leftJoin("e.Translation et WITH lang = '".$this->getUser()->getCulture()."'")
        ->addSelect('(SELECT count(g2.id) FROM Manifestation m2 LEFT JOIN m2.Gauges g2 WHERE m2.id = g.manifestation_id AND g2.id IS NOT NULL) AS nb_ws')
        ->andWhereIn('g.manifestation_id',$criterias['manifestations'])
        ->orderBy('et.name, m.happens_at, ws.name');
      $this->gauges = $q->execute();
    }
