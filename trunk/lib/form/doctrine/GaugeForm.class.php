<?php

/**
 * Gauge form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class GaugeForm extends BaseGaugeForm
{
  public function configure()
  {
    $this->widgetSchema['manifestation_id']->setOption('add_empty',true);
    $this->widgetSchema['workspace_id']->setOption('add_empty',true);
    $this->widgetSchema['workspace_id']->setOption('query',Doctrine::getTable('Workspace')->createQuery()->orderBy('name'));
    $this->validatorSchema['value']->setOption('required',false);
  }
  public function setHidden($hides = array('manifestation_id','workspace_id'))
  {
    foreach ( $hides as $hide )
      $this->widgetSchema[$hide] = new sfWidgetFormInputHidden();
    return $this;
  }
}
