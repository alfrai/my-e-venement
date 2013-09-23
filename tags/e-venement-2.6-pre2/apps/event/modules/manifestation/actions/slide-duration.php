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
    $this->manifestation = Doctrine_Query::create()->from('Manifestation m')
      ->andWhere('m.id = ?',$request->getParameter('id'))
      ->fetchOne();
    $this->forward404Unless($request->hasParameter('days') && $request->hasParameter('minutes') && $this->manifestation);
    
    // manifestation
    $this->manifestation->duration = $str = $this->manifestation->duration +
      $request->getParameter('days') * 24 * 60 * 60 +
      $request->getParameter('minutes') * 60;
    
    // reservation
    $this->manifestation->reservation_ends_at = date('Y-m-d H:i:s',
      strtotime($this->manifestation->reservation_ends_at) +
      $request->getParameter('days') * 24 * 60 * 60 +
      $request->getParameter('minutes') * 60
    );
    
    $this->manifestation->save();
    
    return sfView::NONE;
