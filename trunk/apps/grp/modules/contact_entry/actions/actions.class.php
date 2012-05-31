<?php

require_once dirname(__FILE__).'/../lib/contact_entryGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/contact_entryGeneratorHelper.class.php';

/**
 * contact_entry actions.
 *
 * @package    e-venement
 * @subpackage contact_entry
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class contact_entryActions extends autoContact_entryActions
{
  public function executeDel(sfWebRequest $request)
  {
    $this->getRoute()->getObject()->delete();
    return $this->redirect($this->getModuleName());
  }
  
  public function executeTranspose(sfWebRequest $request)
  {
    $this->contact_entry = $this->getRoute()->getObject();
    if ( !is_null($this->contact_entry->transaction_id) )
      return $this->redirect(cross_app_url_for('tck','ticket/sell?id='.$this->element->transaction_id));
    
    $this->element->Transaction = new Transaction;
    $this->element->Transaction->Professional = $this->element->ContactEntry->Professional;
    $this->element->Transaction->contact_id = $this->element->ContactEntry->Professional->contact_id;
    
    foreach ( $this->element->EntryTickets as $tickets )
    {
      for ( $i = 0 ; $i < $tickets->quantity ; $i++ )
      {
        $price = Doctrine::getTable('Price')->createQuery('p')
          ->leftJoin('PriceManifestation pm')
          ->andWhere('pm.manifestation_id = ?',$this->element->ManifestationEntry->manifestation_id)
          ->andWhere('p.id = ?',$tickets->price_id)
          ->fetchOne();
        
        $ticket = new Ticket();
        $ticket->price_id = $tickets->price_id;
        $ticket->value = $p->PriceManifestation->value;
        $ticket->price_name = $p->name;
        $ticket->manifestation_id = $this->element->ManifestationEntry->manifestation_id;
        $ticket->Transaction = $this->element->Transaction;
        $ticket->gauge_id = $this->element->ManifestationEntry->Manifestation->Gauges[0]->id;
      }
    }
    
    $this->element->save();
    return $this->redirect(cross_app_url_for('tck','ticket/sell?id='.$this->element->Transaction->id));
  }
  
  public function executeUntranspose(sfWebRequest $request)
  {
  }
}
