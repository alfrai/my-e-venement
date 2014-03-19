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
  public $current_version = NULL;
  
  public function getName()
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('I18N','Date'));
    return $this->Event->name.' '.__('at').' '.$this->getShortenedDate();
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
  public function getShortenedDate()
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('Date'));
    return format_datetime($this->happens_at,'EEE d MMM yyyy HH:mm');
  }
  public function getMiniDate()
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('Date'));
    return format_datetime($this->happens_at,'dd/MM/yyyy HH:mm');
  }
  public function getMiniEndDate()
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('Date'));
    return format_datetime($this->ends_at,'dd/MM/yyyy HH:mm');
  }
  public function getShortName()
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('I18N','Date'));
    return $this->Event->name.' '.__('at').' '.format_date($this->happens_at);
  }
  public function getEndsAt()
  {
    return date('Y-m-d H:i:s',strtotime($this->happens_at)+$this->duration);
  }
  public function setEndsAt($ends_at)
  {
    $this->duration = strtotime($ends_at) - strtotime($this->happens_at);
    return $this;
  }
  public function getEndsAtTime()
  {
    return strtotime($this->happens_at)+$this->duration;
  }
  public function getHappensAtTime()
  {
    return strtotime($this->happens_at);
  }
  public function getCreatedAtTime()
  {
    return strtotime($this->created_at);
  }
  public function getUpdatedAtTime()
  {
    return strtotime($this->happens_at);
  }
  public function __toString()
  {
    return $this->getName();
  }
  
  /**
    * method hasAnyConflict()
    * returns if the object is or would be in conflict with an other one
    * concerning the resources management
    *
    * Precondition: the values that are used are those which are recorded in DB
    *
    **/
  public function hasAnyConflict()
  {
    $rids = array();
    foreach ( $this->Booking as $r )
      $rids[] = $r->id;
    $rids[] = $this->location_id;
    
    $m2_start = "CASE WHEN m.happens_at < m.reservation_begins_at THEN m.happens_at ELSE m.reservation_begins_at END";
    $m2_stop  = "CASE WHEN m.happens_at + (m.duration||' seconds')::interval > m.reservation_ends_at THEN m.happens_at + (m.duration||' seconds')::interval ELSE m.reservation_ends_at END";
    $start = $this->happens_at > $this->reservation_begins_at ? $this->reservation_begins_at : $this->happens_at;
    $stop = $this->ends_at > $this->reservation_ends_at ? $this->ends_at : $this->reservation_ends_at;
    
    $q = Doctrine::getTable('Manifestation')->createQuery('m', true)
      ->leftJoin('m.Booking b')
      ->andWhere("$m2_start < ? AND $m2_stop > ?", array($stop, $start))
      ->andWhere('m.reservation_confirmed = ?', true)
      ->andWhere('m.blocking = ?', true)
      ->andWhere('(TRUE')
      ->andWhereIn('b.id',$rids)
      ->orWhereIn('m.location_id',$rids)
      ->andWhere('TRUE)');
    
    return $q->count() > 0;
  }
  
  /**
    * Get all needed informations about the manifestation's gauges usage
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
        
    if ( isset($options['workspaces']) && $options['workspaces'] )
      $q->leftJoin('tck.Gauge g')
        ->andWhereIn('g.workspace_id',$options['workspaces']);
    
    if (!( isset($options['not-yet-printed']) && $options['not-yet-printed']))
      $q->andWhere('(tck.printed_at IS NOT NULL OR tck.cancelling IS NOT NULL OR tck.integrated_at IS NOT NULL)');
    else
      $q->leftJoin('tck.Transaction t')
        ->leftJoin('t.Payments p')
        ->andWhere('p.id IS NOT NULL');
    
    if ( isset($options['dates']) && is_array($options['dates']) )
    {
      if (!( isset($options['tck_value_date_payment']) && $options['tck_value_date_payment'] ))
        $q->andWhere('tck.printed_at IS NOT NULL AND tck.printed_at >= ? AND tck.printed_at < ? OR integrated_at IS NOT NULL AND tck.integrated_at >= ? AND tck.integrated_at < ? OR tck.cancelling IS NOT NULL AND tck.created_at >= ? AND tck.created_at < ?',array(
            $options['dates'][0], $options['dates'][1],
            $options['dates'][0], $options['dates'][1],
            $options['dates'][0], $options['dates'][1],
          ));
      else
      {
        if ( !$q->contains('LEFT JOIN t.Payments p') )
          $q->leftJoin('tck.Transaction t')
            ->leftJoin('t.Payments p');
        $q->andWhere('p.created_at >= ? AND p.created_at < ?',array(
            $options['dates'][0],
            $options['dates'][1],
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
    
    $r = array('value' => 0, 'qty' => 0, 'vat' => array());
    foreach ( $tickets as $ticket )
    {
      $r['value'] += $ticket['value'];
      $r['qty']   += is_null($ticket['cancelling']) ? 1 : -1;
      
      if ( !isset($r['vat'][$ticket['vat']]) )
        $r['vat'][$ticket['vat']] = 0;
      
      // extremely weird behaviour, only for specific cases... it's about an early error in the VAT calculation in e-venement
      $r['vat'][$ticket['vat']] += sfConfig::get('app_ledger_sum_rounding_before',false) && strtotime($ticket['printed_at']) < sfConfig::get('app_ledger_sum_rounding_before')
        ? $ticket['value'] - $ticket['value'] / (1+$ticket['vat'])
        : round($ticket['value'] - $ticket['value'] / (1+$ticket['vat']),2);
    }
    
    // rounding VAT
    foreach ( $r['vat'] as $rate => $value )
      $r['vat'][$rate] = round($value,2);
    
    return $r;
  }
}
