<?php

/**
 * ManifestationExtraInformation form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ManifestationExtraInformationForm extends BaseManifestationExtraInformationForm
{
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    $this->widgetSchema['manifestation_id'] = new sfWidgetFormInputHidden();
    $this->widgetSchema['sf_guard_user_id'] = new sfWidgetFormInputHidden();
    $this->widgetSchema['version']          = new sfWidgetFormInputHidden();
    
    $this->widgetSchema['name']   ->setLabel('Title');
    $this->widgetSchema['value']  ->setLabel('Description');
    $this->widgetSchema['checked']->setLabel('Validated');
  }
}
