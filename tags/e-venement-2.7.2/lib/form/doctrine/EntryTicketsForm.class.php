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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

/**
 * EntryTickets form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EntryTicketsForm extends BaseEntryTicketsForm
{
  const CACHE_TIMEOUT = 5; // timeout, in minutes
  
  public function configure()
  {
    $this->widgetSchema['entry_element_id'] = new sfWidgetFormInputHidden();
    
    $this->widgetSchema['price_id']->setOption('add_empty', true);
    
    $this->validatorSchema['gauge_id']->setOption('query', $q = Doctrine::getTable('Gauge')
      ->createQuery('g')
      ->select('g.*, w.*')
      ->leftJoin('g.Manifestation m')
      ->leftJoin('m.ManifestationEntries me')
      ->leftJoin('me.Entries ee')
      ->leftJoin('g.Workspace w')
      ->leftJoin('w.GroupWorkspace gw')
      ->andWhere('gw.id IS NOT NULL')
    );
    $this->widgetSchema   ['gauge_id']
      ->setOption('query', $q->copy())
      ->setOption('order_by', array('w.name', ''));
    if ( !$this->isNew() )
      $this->widgetSchema ['gauge_id'] = new sfWidgetFormInputHidden;

    $arr = array('id','entry_element_id','quantity','price_id', 'gauge_id');
    $this->widgetSchema->setPositions($arr);
    $this->enableCSRFProtection();
  }
  
  public function doBind(array $values)
  {
    $this->restrictGaugeIdQuery($this->validatorSchema['entry_element_id']->clean($values['entry_element_id']));
    $this->restrictPriceIdQuery($this->validatorSchema['entry_element_id']->clean($values['entry_element_id']));
    parent::doBind($values);
  }
  
  public function restrictGaugeIdQuery($entry_element_id = NULL)
  {
    if (!( $this->widgetSchema['gauge_id'] instanceof sfWidgetFormDoctrineChoice ))
      return;
    
    $manifid = !is_null($entry_element_id) && $this->object->isNew()
      ? Doctrine::getTable('EntryElement')->findOneById($entry_element_id)->ManifestationEntry->manifestation_id
      : $this->getObject()->EntryElement->ManifestationEntry->manifestation_id;
    
    if ( $this->widgetSchema['gauge_id']->getOption('query') instanceof Doctrine_Query )
      $this->widgetSchema   ['gauge_id']->getOption('query')->andWhere('g.manifestation_id = ?', $manifid);
    $this->validatorSchema  ['gauge_id']->getOption('query')->andWhere('g.manifestation_id = ?', $manifid);
    
    // backup gauges for current manifestation
    if ( sfContext::hasInstance() )
    {
      $this->timeout();
      $gauges = sfContext::getInstance()->getUser()->getAttribute('gauges', array(), 'grp');
      if ( !isset($gauges[$manifid]) && $this->widgetSchema['gauge_id'] instanceof sfWidgetFormDoctrineChoice )
      {
        $gauges[$manifid] = $this->widgetSchema['gauge_id']->getChoices();
        sfContext::getInstance()->getUser()->setAttribute('gauges', $gauges, 'grp');
      }
      $this->widgetSchema['gauge_id'] = new sfWidgetFormChoice(array('choices' => $gauges[$manifid],));
    }
    
    return $this;
  }
  
  public function restrictPriceIdQuery($entry_element_id = NULL)
  {
    if (!( $this->widgetSchema['price_id'] instanceof sfWidgetFormDoctrineChoice ))
      return;
    
    if ( !is_null($entry_element_id) && $this->object->isNew() )
    {
      $entry_element = Doctrine::getTable('EntryElement')->findOneById(
        intval($entry_element_id) > 0
          ? $entry_element_id
          : ($this->values['entry_element_id'] ? $this->values['entry_element_id'] : $this->getObject()->entry_element_id)
      );
    }
    else
      $entry_element = $this->getObject()->EntryElement;
    
    $manifid = $entry_element->ManifestationEntry->manifestation_id;
    
    $this->widgetSchema['price_id']->setOption('query', $q = Doctrine::getTable('Price')
      ->createQuery('p')
      ->select('p.*')
      ->leftJoin('p.Users u')
      ->andWhere('u.id = ?',sfContext::getInstance()->getUser()->getId())
      ->leftJoin('p.Manifestations m')
      ->andWhere('m.id = ?', $manifid)
      ->andWhere('p.hide = FALSE')
      ->orderBy('p.name')
    )->setOption('order_by', array('p.name',''));
    $this->validatorSchema['price_id']->setOption('query',$q);
    
    // backup prices for current manifestation
    if ( sfContext::hasInstance() )
    {
      $this->timeout();
      $prices = sfContext::getInstance()->getUser()->getAttribute('prices', array(), 'grp');
      if ( !isset($prices[$manifid]) && $this->widgetSchema['price_id'] instanceof sfWidgetFormDoctrineChoice )
      {
        $prices[$manifid] = $this->widgetSchema['price_id']->getChoices();
        sfContext::getInstance()->getUser()->setAttribute('prices', $prices, 'grp');
      }
      $this->widgetSchema['price_id'] = new sfWidgetFormChoice(array('choices' => $prices[$manifid],));
    }
    
    return $this;
  }
  
  // force the recalculation every 2*60 seconds
  public function timeout()
  {
    if ( !sfContext::hasInstance() )
      return true;
    
    if ( time() < sfContext::getInstance()->getUser()->getAttribute('timeout', time(), 'grp') )
      return false;
    
    sfContext::getInstance()->getUser()->setAttribute('gauges', array(), 'grp');
    sfContext::getInstance()->getUser()->setAttribute('prices', array(), 'grp');
    sfContext::getInstance()->getUser()->setAttribute('timeout', time() + self::CACHE_TIMEOUT * 60, 'grp');
    return true;
  }
}

