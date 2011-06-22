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
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    
    $this->validatorSchema['nb'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['duplicate'] = new sfValidatorInteger(array('min' => 0, 'required' => false));
    $this->validatorSchema['price_id']->setOption('required',false);
    $this->validatorSchema['value']->setOption('required',false);
    $this->validatorSchema['sf_guard_user_id']->setOption('required',false);
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputHidden();
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
      $q = Doctrine::getTable('Ticket')
        ->createQuery('t')
        ->leftJoin('t.Price p')
        ->andWhere('t.manifestation_id = ?', $this->object->manifestation_id)
        ->andWhere('t.transaction_id = ?', $this->object->transaction_id)
        ->andWhere('p.name = ?', $this->object->price_name)
        ->andWhere('NOT t.printed')
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
      {
        try {
          foreach ( $params['manifestation_id'] as $manifestation_id )
          {
            $this->object->manifestation_id = $manifestation_id;
            $this->object->save();
            $tickets[] = $this->object;
            $this->object = $this->object->copy();
          }
        }
        catch ( Doctrine_Connection_Pgsql_Exception $e )
        {
          return $tickets;
        }
      }
    }
    
    return $tickets;
  }
}
