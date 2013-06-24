<?php

/**
 * Transaction form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TransactionTicketForm extends TransactionForm
{
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    
    $this->validatorSchema['manifestation_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'query' => Doctrine::getTable('Manifestation')->createQuery('m')
        ->leftJoin('m.Transaction t'),
    ));
    
    $this->validatorSchema['price_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'PriceManifestation',
      'query' => Doctrine::getTable('PriceManifestation')->createQuery('pm')
        ->leftJoin('p.Transaction t'),
    ));
  }
}
