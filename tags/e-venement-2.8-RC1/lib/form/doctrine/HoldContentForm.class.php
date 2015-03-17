<?php

/**
 * HoldContent form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class HoldContentForm extends BaseHoldContentForm
{
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    
    $this->validatorSchema['hold_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Hold',
    ));
    $this->validatorSchema['seat_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Seat',
    ));
  }
  
  public function isValid()
  {
    if ( !parent::isValid() )
      return false;
    
    // check if this seat is not held by an other hold for the same manifestation
    $q = Doctrine::getTable('Hold')->createQuery('h',true)
      ->andWhere('h.id != ? AND h.manifestation_id = (SELECT hh.manifestation_id FROM Hold hh WHERE hh.id = ?)', array($this->values['hold_id'], $this->values['hold_id']))
      ->leftJoin('h.HoldContents hc')
      ->andWhere('hc.seat_id = ?', $this->values['seat_id'])
    ;
    return $q->count() == 0;
  }
}
