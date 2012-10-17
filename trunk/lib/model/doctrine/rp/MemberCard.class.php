<?php

/**
 * MemberCard
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class MemberCard extends PluginMemberCard
{
  protected $value;
  
  public function __toString()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('Number');
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Date'));
    return __($this->name).' #'.$this->id."\n(".format_date($this->expire_at,'D').($this->value > 0 ? ', '.format_currency($this->value,'€') : '').')';
  }
  
  public function getValue()
  {
    if ( isset($this->value) )
      return $this->value;
    
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $q = "SELECT -sum(value) AS value FROM payment WHERE member_card_id = :member_card_id";
    $stmt = $pdo->prepare($q);
    $stmt->execute(array('member_card_id' => $this->id));
    $rec = $stmt->fetch();
    return $this->value = $rec['value'] ? $rec['value'] : 0;
  }
  
  public function postInsert($event)
  {
    // prices
    $q = Doctrine::getTable('MemberCardPriceModel')->createQuery('pm')
      ->andWhere('UPPER(pm.member_card_name) = UPPER(?)',$this->name);
    $models = $q->execute();
    
    foreach ( $models as $model )
    for ( $i = 0 ; $i < $model->quantity ; $i++ )
    {
      $mc_price = new MemberCardPrice;
      $mc_price->price_id = $model->price_id;
      $mc_price->event_id = $model->event_id;
      $mc_price->member_card_id = $this->id;
      $mc_price->save();
    }
    
    parent::postInsert($event);
  }
  
  public function delete(Doctrine_Connection $con = NULL)
  {
    if ( $this->Payments->count() == 0 && $this->Tickets->count() == 0 )
      return parent::delete($con);
    
    $payments = $tickets = 0;
    
    foreach ( $this->Tickets as $ticket )
      $tickets += is_null($ticket->cancelling)*2-1;
    foreach ( $this->Payments as $payment )
      $payments += $payment->value;
    
    if ( $tickets == 0 && $payments == 0 )
    {
      $this->active = false;
      return parent::save($con);
    }
    
    throw new liEvenementException('The member card cannot be deleted neither deactivated.');
  }
}
