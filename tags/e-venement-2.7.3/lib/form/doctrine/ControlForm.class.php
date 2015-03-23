<?php

/**
 * Control form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ControlForm extends BaseControlForm
{
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    
    unset($this->widgetSchema['sf_guard_user_id']);
    unset($this->widgetSchema['version']);
    
    $this->validatorSchema['sf_guard_user_id']->setOption('required', false);
    $this->validatorSchema['version']->setOption('required', false);
    
    $this->widgetSchema['checkpoint_id']->setOption('add_empty',true);
    
    $this->widgetSchema['ticket_id'] = new sfWidgetFormInput();
    $this->widgetSchema['comment'] = new sfWidgetFormTextArea();
    
    if ( sfConfig::get('app_tickets_id') != 'id' )
    {
      $this->validatorSchema['ticket_id'] = new sfValidatorDoctrineChoice(array(
        'model' => 'Ticket',
        'column' => sfConfig::get('app_tickets_id'),
      ));
    }
  }
}
