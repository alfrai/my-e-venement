<?php

/**
 * Manifestation
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Manifestation extends PluginManifestation
{
  public function getName()
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('I18N','Date'));
    return $this->Event->name.' '.__('at').' '.format_datetime($this->happens_at,'EEE d MMM yyyy HH:mm');
  }
  public function getNameWithFullDate()
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('I18N'));
    return $this->Event->name.' '.__('at').' '.$this->getFormattedDate();
  }
  public function getFormattedDate()
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('Date'));
    return format_datetime($this->happens_at,'EEEE d MMMM yyyy HH:mm');
  }
  public function getShortName()
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('I18N','Date'));
    return $this->Event->name.' '.__('at').' '.format_date($this->happens_at);
  }
  public function __toString()
  {
    return $this->getName();
  }
  
  /**
    * $options: modeled on sales ledger's criterias
    * 
    **/
  public function getInfosTickets($options = array())
  {
    if ( (isset($options['dates'][0]) || isset($options['dates'][1])) && (!isset($options['dates'][0]) || !isset($options['dates'][1])) )
      unset($options['dates']);
    
    $q = Doctrine::getTable('Ticket')->createQuery('tck')
      ->andWhere('manifestation_id = ?',$this->id)
      ->andWhere('tck.duplicating IS NULL');
        
    if ( $options['workspaces'] )
      $q->leftJoin('tck.Gauge g')
        ->andWhereIn('g.workspace_id',$options['workspaces']);
    
    if (!( isset($options['not-yet-printed']) && $options['not-yet-printed']))
      $q->andWhere('tck.printed = TRUE OR tck.cancelling IS NOT NULL OR tck.integrated = TRUE');
    else
      $q->leftJoin('tck.Transaction t')
        ->leftJoin('t.Payments p')
        ->andWhere('p.id IS NOT NULL');
    
    if ( isset($options['dates']) )
    {
      if ( !$options['tck_value_date_payment'] )
        $q->andWhere('tck.updated_at >= ? AND tck.updated_at < ?',array(
            date('Y-m-d',$options['dates'][0]),
            date('Y-m-d',$options['dates'][1]),
          ));
      else
      {
        if ( !$q->contains('LEFT JOIN t.Payments p') )
          $q->leftJoin('tck.Transaction t')
            ->leftJoin('t.Payments p');
        $q->andWhere('p.created_at >= ? AND p.created_at < ?',array(
            date('Y-m-d',$options['dates'][0]),
            date('Y-m-d',$options['dates'][1]),
          ))
          ->andWhere('p.id = (SELECT min(id) FROM Payment p2 WHERE transaction_id = t.id)');
      }
    }
    
    if ( sfContext::hasInstance()
      && !sfContext::getInstance()->getUser()->hasCredential('tck-ledger-all-users')
      && $context = sfContext::getInstance() )
      $q->andWhere('tck.sf_guard_user_id = ?',$context->getUser()->getId());
    else if ( isset($options['users']) && is_array($options['users']) && $options['users'][0] )
    {
      if ( $options['users'][''] ) unset($options['users']['']);
      if ( !isset($criterias['tck_value_date_payment']) )
        $q->andWhereIn('tck.sf_guard_user_id',$options['users']);
      else
      {
        if ( !$q->contains('LEFT JOIN t.Payments p') )
          $q->leftJoin('tck.Transaction t')
            ->leftJoin('t.Payments p');
        $q->andWhereIn('p.sf_guard_user_id',$options['users']);
      }
    }

    $tickets = $q->fetchArray();
    
    $r = array('value' => 0, 'qty' => 0);
    foreach ( $tickets as $ticket )
    {
      $r['value'] += $ticket['value'];
      $r['qty']   += is_null($ticket['cancelling']) ? 1 : -1;
    }
    
    return $r;
  }
  
  public function postInsert($event)
  {
    if ( $this->PriceManifestations->count() == 0 )
    foreach ( Doctrine::getTable('Price')->createQuery()->execute() as $price )
    {
      $pm = PriceManifestation::createPrice($price);
      $pm->manifestation_id = $this->id;
      //$pm->save();
      $this->PriceManifestations[] = $pm;
    }
    $this->save();
    
    parent::postInsert($event);
  }
}
