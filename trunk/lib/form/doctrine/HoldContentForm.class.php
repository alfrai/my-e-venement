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
}
