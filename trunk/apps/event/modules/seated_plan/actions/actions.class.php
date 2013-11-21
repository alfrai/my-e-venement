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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require_once dirname(__FILE__).'/../lib/seated_planGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/seated_planGeneratorHelper.class.php';

/**
 * seated_plan actions.
 *
 * @package    e-venement
 * @subpackage seated_plan
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class seated_planActions extends autoSeated_planActions
{
  public function executeSeatAdd(sfWebRequest $request)
  {
    if (!( $data = $request->getParameter('seat',array() ))
      throw new liSeatingException('Given data do not permit the seat recording (no data).');
    if ( !isset($data['x']) || !isset($data['y']) || !isset($data['diameter']) || !isset($data['name']) )
      throw new liSeatingException('Given data do not permit the seat recording (bad data).');
    
    $seat = new Seat;
    foreach ( array('name', 'x', 'y', 'diameter') as $fieldName )
      $seat->$fieldName = $data[$fieldName];
    $seat->save();
    
    return sfView::NONE;
  }
  
  public function executeDelPicture(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()->from('Picture p')
      ->where('p.id IN (SELECT s.picture_id FROM SeatedPlan s WHERE s.id = ?)',$request->getParameter('id'))
      ->delete()
      ->execute();
    return $this->redirect('seated_plan/edit?id='.$request->getParameter('id'));
  }
}
