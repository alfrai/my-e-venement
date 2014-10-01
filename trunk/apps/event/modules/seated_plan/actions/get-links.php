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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

    $this->preLinks($request);
    
    $this->data = array();
    foreach ( $this->getRoute()->getObject()->getLinks() as $link )
    {
      $a = $link[0];
      $b = $link[1];
      
      $ab = sqrt(pow($a->x-$b->x,2) + pow($a->y-$b->y,2));
      $ac = $a->x-$b->x;
      $bc = $a->y-$b->y;
      
      if ( $ac == 0 && $bc != 0 )
        $angle = $a->y < $b->y ? 90 : 270;
      else
      {
        $preangle = rad2deg(atan($bc/$ac));
        if ( $a->x <= $b->x && $a->y <= $b->y )
          $angle =   0 + $preangle;
        else
        if ( $a->x >  $b->x && $a->y <= $b->y )
          $angle = 180 + $preangle;
        else
        if ( $a->x >  $b->x && $a->y >  $b->y )
          $angle = 180 + $preangle;
        else
        if ( $a->x <= $b->x && $a->y >  $b->y )
          $angle = 360 + $preangle;
      }
      
      $this->data[] = array(
        'type'  => 'link',
        'names' => array($a->name, $b->name),
        'ids'   => array($a->id, $b->id),
        'coordinates' => array(
          array($a->x, $a->y),
          array($b->x, $b->y),
        ),
        'angle' => $angle, // in degrees
        'length' => $ab,
      );
    }
    
    if ( sfConfig::get('sf_web_debug', false) && $request->getParameter('debug') )
      return $this->renderText(print_r($this->data));
    return 'Success';
