<?php

/**
 * Ticket form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TicketForm extends BaseTicketForm
{
  public function getCSRFToken($secret = '')
  {
    return md5(php_uname().session_id().get_class($this));
  }
  
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    
    $this->validatorSchema['nb'] = new sfValidatorInteger(array('required' => false));
    //$this->validatorSchema['duplicate'] = new sfValidatorInteger(array('min' => 0, 'required' => false));
    $this->validatorSchema['price_id']->setOption('required',false);
    $this->validatorSchema['value']->setOption('required',false);
    $this->validatorSchema['gauge_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Gauge',
      'required' => true,
      'query' => Doctrine::getTable('Gauge')->createQuery('g')->andWhereIn('g.workspace_id',array_keys(sfContext::getInstance()->getUser()->getWorkspacesCredentials())),
    ));
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputHidden();
    $this->widgetSchema['numerotation'] = new sfWidgetFormInputHidden();
  }
  
  public function isValid()
  {
    if ( !parent::isValid() )
      return false;
    
    $q = Doctrine::getTable('Workspace')->createQuery('w')
      ->leftJoin('w.Gauges g')
      ->andWhere('g.id = ?',$this->getValue('gauge_id'));
    $workspace = $q->fetchOne();
    if ( $workspace->seated )
    {
      if ( !$this->getValue('numerotation') )
        throw new liSeatingException('No numerotation given.');
      
      $q = Doctrine::getTable('Ticket')->createQuery('tck')
        ->andWhere('tck.numerotation = ?',$this->getValue('numerotation'))
        ->andWhere('tck.gauge_id = ?',$this->getValue('gauge_id'))
        ->andWhere('tck.cancelling IS NULL AND tck.id NOT IN (SELECT tt.cancelling FROM Ticket tt WHERE tt.cancelling IS NOT NULL AND tt.gauge_id = tck.gauge_id)');
      $tickets = $q->execute();
      if ( $this->getValue('nb') < 0 && $tickets->count() == 0 )
        throw new liSeatingException('There is no ticket to remove on this seat for this gauge.');
      if ( $this->getValue('nb') > 0 && $tickets->count() > 0 )
        throw new liSeatingException('There are already some tickets on this seat for this gauge.');
    }

    return true;
  }
  
  public function save($con = NULL)
  {
    $params = $this->getValues();
    $nb = isset($params['nb']) && $params['nb'] != 0 ? $params['nb'] : 1;
    unset($params['nb']);
    
    if ( !is_array($params['manifestation_id']) )
      $params['manifestation_id'] = array($params['manifestation_id']);
    
    foreach ( $params as $name => $param )
      if ( $name != 'manifestation_id' )
        $this->object->$name = $param;
    
    if ( $nb < 0 )
    {
      $this->object->manifestation_id = $params['manifestation_id'][0];
      $q = Doctrine::getTable('Ticket')->createQuery('t')
        ->leftJoin('t.Price p')
        ->andWhere('t.manifestation_id = ?', $this->object->manifestation_id)
        ->andWhere('t.transaction_id = ?', $this->object->transaction_id)
        ->andWhere('p.name = ?', $this->object->price_name)
        ->andWhere('t.printed_at IS NULL')
        ->andWhere('t.gauge_id = ?',$this->object->gauge_id)
        ->orderBy('t.integrated_at, t.id DESC')
        ->limit(-$nb);
      $tickets = $q->execute();
      foreach ( $tickets as $ticket )
        $ticket->delete();
      return array();
    }
    else
    {
      $tickets = array();
      for ( $i = 0 ; $i < $nb ; $i++ )
      foreach ( $params['manifestation_id'] as $manifestation_id )
      try {
        $this->object->manifestation_id = $manifestation_id;
        $this->object->save();
        $tickets[] = $this->object;
        $this->object = $this->object->copy();
      }
      catch ( liEvenementException $e )
      { }
      catch ( Doctrine_Connection_Pgsql_Exception $e )
      { return $tickets; }
    }
    
    return $tickets;
  }
}
