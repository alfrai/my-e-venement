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
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink'));
    
    $this->contact_entry = $this->getRoute()->getObject();
    if ( !is_null($this->contact_entry->transaction_id) )
      return $this->redirect(cross_app_url_for('tck','ticket/sell?id='.$this->contact_entry->transaction_id));
    
    $this->contact_entry->Transaction = new Transaction;
    $this->contact_entry->Transaction->Professional = $this->contact_entry->Professional;
    $this->contact_entry->Transaction->contact_id = $this->contact_entry->Professional->contact_id;
    
    foreach ( $this->contact_entry->Entries as $element )
    if ( $element->accepted )
    foreach ( $element->EntryTickets as $tickets )
    {
      for ( $i = 0 ; $i < $tickets->quantity ; $i++ )
      {
        $price = Doctrine::getTable('PriceManifestation')->createQuery('pm')
          ->leftJoin('pm.Price p')
          ->andWhere('pm.manifestation_id = ?',$element->ManifestationEntry->manifestation_id)
          ->andWhere('p.id = ?',$tickets->price_id)
          ->fetchOne();
        
        $ticket = new Ticket();
        $ticket->price_id = $tickets->price_id;
        $ticket->value = $price->value;
        $ticket->price_name = $price->Price->name;
        $ticket->manifestation_id = $element->ManifestationEntry->manifestation_id;
        $this->contact_entry->Transaction->Tickets[] = $ticket;
        
        /*
        $gauge = Doctrine::getTable('Gauge')->createQuery('g')
          ->leftJoin('g.Workspace w')
          ->leftJoin('w.GroupWorkspace gw')
          ->andWhere('g.manifestation_id = ?',$element->ManifestationEntry->Manifestation->id)
          ->andWhere('gw.id IS NOT NULL')
          ->andWhere('g.id = ?', $tickets->gauge_id)
          ->fetchOne();
        if ( !$gauge )
        {
          $this->getContext()->getConfiguration()->loadHelpers('I18N');
          $this->getUser()->setFlash('error',__('Transposition failed: no gauge available on this manifestation for the groups module'));
          $this->redirect('professional/view?id='.$this->contact_entry->professional_id);
        }
        */
        $ticket->gauge_id = $tickets->gauge_id;
      }
    }
    
    $this->contact_entry->save();
    return $this->redirect(cross_app_url_for('tck','ticket/sell?id='.$this->contact_entry->Transaction->id));
  }
  
  public function executeUntranspose(sfWebRequest $request)
  {
    $this->contact_entry = $this->getRoute()->getObject();
    $this->contact_entry->transaction_id = NULL;
    $this->contact_entry->save();
    $this->redirect('event/edit?id='.$this->contact_entry->Entry->event_id);
  }
}
