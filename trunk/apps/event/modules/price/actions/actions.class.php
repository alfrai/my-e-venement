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

require_once dirname(__FILE__).'/../lib/priceGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/priceGeneratorHelper.class.php';

/**
 * price actions.
 *
 * @package    e-venement
 * @subpackage price
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class priceActions extends autoPriceActions
{
  public function executeAjax(sfWebRequest $request)
  {
    $charset = sfConfig::get('software_internals_charset');
    $search  = iconv($charset['db'],$charset['ascii'],strtolower($request->getParameter('q')));
    $this->json = array();
    
    if ( !$search )
      return 'Json';
    
    if (!( $max = $request->getParameter('max',sfConfig::get('app_manifestations_max_ajax')) ))
      $max = 10;
    
    $q = Doctrine::getTable('Price')->createQuery('p')
      ->andWhere('pt.lang = ?', $this->getUser()->getCulture())
      ->andWhere('pt.name ILIKE ?', $search.'%')
      ->orderBy('pt.name')
      ->limit($request->getParameter('limit',$max));
    
    foreach ( $q->execute() as $price )
    {
      $this->getContext()->getConfiguration()->loadHelpers('Url');
      $this->json[$price->id] = (string)$price;
    }
    
    if ( $request->hasParameter('debug') && sfConfig::get('sf_web_debug', false) )
      return 'Success';
    else
      return 'Json';
  }
}
