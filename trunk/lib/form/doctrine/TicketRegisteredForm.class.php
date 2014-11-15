<?php

/**
 * Ticket form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TicketRegisteredForm extends TicketForm
{
  public function configure()
  {
    parent::configure();
    foreach ( $this->widgetSchema->getFields() as $name => $widget )
    if ( !in_array($name, $fields = array('contact_id', 'id', 'transaction_id', 'comment')) )
      unset($this->widgetSchema[$name]);
    foreach ( $this->validatorSchema->getFields() as $name => $validator )
    if ( !in_array($name, $fields) )
      unset($this->validatorSchema[$name]);
    
    $this->widgetSchema   ['reduc'] = new sfWidgetFormInput(array(), array('pattern' => $pattern = '\d+([\.,]\d{0,2}){0,1}%{0,1}'));
    $this->validatorSchema['reduc'] = new sfValidatorRegex(array('pattern' => '/'.$pattern.'/', 'required' => false));
    
    $this->validatorSchema['transaction_id']->setOption('query',
      Doctrine::getTable('Transaction')->createQuery('t')
        ->andWhere('t.closed = ?', false)
    );
    
    if ( !$this->object->isNew() )
      $this->widgetSchema->setNameFormat('ticket['.$this->object->id.'][%s]');
  }
  
  protected function doBind(array $values)
  {
    $this->validatorSchema['id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Ticket',
      'query' => Doctrine::getTable('Ticket')->createQuery('tck')
        ->andWhere('tck.transaction_id = ?', $values['transaction_id']),
      'required' => true,
    ));
    return parent::doBind($values);
  }
  
  public function save($con = NULL)
  {
    if (!( $this->object = Doctrine::getTable('Ticket')->find($this->values['id']) ))
      throw new liEvenementException('To register a ticket, it needs to exist first');
    if ( $this->object->transaction_id != $this->values['transaction_id'] )
      throw new liEvenementException("You must register a ticket on your current transaction (#{$this->values['transaction_id']}), not #{$this->object->transaction_id}");
    
    if ( $this->values['reduc'] )
    {
      $reduc = $this->values['reduc'];
      if ( $reduc[strlen($reduc)-1] == '%' )
        $this->object->value = round($this->object->value*(1-substr($reduc, 0, strlen($reduc)-1)/100),2);
      else
        $this->object->value = $this->object->value - round(floatval(str_replace(',', '.', $reduc)),2);
      if ( $this->object->value < 0 )
        $this->object->value = 0;
    }
    
    $this->object->contact_id = $this->values['contact_id'];
    $this->object->save();
    return $this->object;
  }
  
  public function getStylesheets()
  {
    return parent::getStylesheets() + array(
      'tck-registered' => 'all',
    );
  }
}
