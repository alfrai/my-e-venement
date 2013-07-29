<?php

/**
 * Batch Control form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class BatchControlForm extends sfForm
{
  public function configure()
  {
    parent::configure();
    $this->widgetSchema->setNameFormat('control[%s]');
    
    $this->widgetSchema   ['manifestation_id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['manifestation_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
    ));
    
    $this->widgetSchema   ['checkpoint_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Checkpoint',
    ));
    $this->validatorSchema['checkpoint_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Checkpoint',
    ));
    
    $this->widgetSchema   ['type'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['type'] = new sfValidatorPass();
  }
}
