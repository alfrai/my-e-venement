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
    $this->validatorSchema['numerotation'] = new sfValidatorString(array(
      'min_length' => 1,
      'required' => false,
    ));
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
        ->orderBy('t.integrated_at DESC, t.id DESC')
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
      catch ( Doctrine_Connection_Exception $e )
      { return $tickets; }
    }
    
    return $tickets;
  }
}
