<?php

/**
 * Gauge form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PricesPublicForm extends BaseFormDoctrine
{
  public function save($con = NULL)
  {
    $values = $this->getValues();
    
    if ( $this->object->isNew() )
      $this->object->save();
    
    $ids = array();
    for ( $i = 0 ; $i < $values['quantity'] ; $i++ )
    {
      $ticket = new Ticket;
      $ticket->price_id = $values['price_id'];
      $ticket->gauge_id = $values['gauge_id'];
      $ticket->Transaction = $this->object;
      $ticket->save();
      $ids[] = $ticket->id;
    }
    
    // removing old tickets, to be replaced
    Doctrine_Query::create()->from('Ticket tck')
      ->andWhere('tck.price_id = ?',$values['price_id'])
      ->andWhere('tck.gauge_id = ?',$values['gauge_id'])
      ->andWhere('tck.transaction_id = ?',$this->object->id)
      ->andWhereNotIn('tck.id',$ids)
      ->delete()
      ->execute();
    
    return $this->object;
  }
  
  public function getModelName()
  {
    return 'Transaction';
  }
  
  public function updateObject($values = NULL)
  {
    if ( isset($values['id']) )
      //$this->object = Doctrine::getTable('Transaction')->createQuery('t')
      $this->object = Doctrine_Query::create()->from('Transaction t')
        ->andWhere('t.id = ?',$values['id'])
        ->fetchOne();
    else
      $this->object = new Transaction;
    
    return $this;
  }
  
  public function configure()
  {
    $this->widgetSchema   ['id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['id'] = new sfValidatorInteger(array('required' => false));
    
    $q = Doctrine::getTable('Gauge')->createQuery('g')
      ->leftJoin('ws.Users u')
      ->andWhere('u.id = ?',sfContext::getInstance()->getUser()->getId());
    $this->widgetSchema   ['gauge_id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['gauge_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Gauge',
      'query' => $q,
    ));
    
    $this->widgetSchema   ['price_id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['price_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Price',
    ));

    $q = Doctrine_Query::create()->from('Transaction t')
      ->andWhere('t.closed = FALSE')
      ->andWhere('t.sf_guard_user_id = ?',sfContext::getInstance()->getUser()->getId());
    $this->widgetSchema   ['transaction_id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['transaction_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Transaction',
      'required' => false,
      'query' => $q,
    ));
    
    $choices = array();
    for ( $i = 0 ; $i <= sfConfig::get('app_tickets_max_per_manifestation',9) ; $i++ )
      $choices[] = $i;
    $this->widgetSchema   ['quantity'] = new sfWidgetFormChoice(array(
      'choices' => $choices,
    ));
    $this->validatorSchema['quantity'] = new sfValidatorChoice(array(
      'choices' => $choices,
    ));
    
    $this->reviewNameFormat();
  }
  
  public function setMaxQuantity($qty)
  {
    if ( $qty < 0 )
      throw new liEvenementException('You cannot set less than 0 as quantity');
    
    $choices = array();
    for ( $i = 0 ; $i <= $qty ; $i++ ) $choices[] = $i;
    $this->widgetSchema   ['quantity']->setOption('choices',$choices);
    $this->validatorSchema['quantity']->setOption('choices',$choices);
    
    return $this;
  }
  
  public function setQuantity($qty)
  {
    if ( $qty < 0 && $qty > count($this->widgetSchema['quantity']->getOption('choices')) )
      throw new liEvenementException('You cannot select a quantity up to max quantity and less than 0');
    
    $this->setDefault('quantity',$qty);
    return $this;
  }
  
  public function setGaugeId($id)
  {
    if ( $id < 1 )
      throw new liEvenementException("Invalid gauge's id");
    
    $this->setDefault('gauge_id',$id);
    $this->reviewNameFormat();
    return $this;
  }
  
  public function setPriceId($id)
  {
    if ( $id < 1 )
      throw new liEvenementException("Invalid price's id");
    
    $this->setDefault('price_id',$id);
    $this->reviewNameFormat();
    return $this;
  }
  
  protected function reviewNameFormat()
  {
    if ( $this->getDefault('price_id') && $this->getDefault('gauge_id') )
      $this->widgetSchema->setNameFormat('price['.$this->getDefault('gauge_id').']['.$this->getDefault('price_id').'][%s]');
    else
      $this->widgetSchema->setNameFormat('price[%s]');
    
    return $this;
  }
}
