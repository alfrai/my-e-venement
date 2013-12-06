<?php

/**
 * ManifestationTemplating form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ManifestationTemplatingForm extends BaseFormDoctrine
{
  public function configure()
  {
    $this->widgetSchema->setNameFormat('template[%s]');
    
    // the template
    $this->widgetSchema['manifestation_model'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Manifestation',
      'url' => url_for('manifestation/ajax'),
      'config' => '{ max: '.sfConfig::get('app_manifestation_depends_on_limit',10).' }',
    ));
    $this->validatorSchema['manifestation_model'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
    ));
    
    // where to applicate it
    $this->widgetSchema['manifestations_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Manifestation',
      'url' => url_for('manifestation/ajax?later=1'),
      'config' => '{ max: '.sfConfig::get('app_manifestation_depends_on_limit',10).' }',
    ));
    $this->validatorSchema['manifestations_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'multiple' => true,
    ));
  }
  
  public function save($con = null)
  {
    $values = $this->getValues();
    $q = Doctrine::getTable('PriceManifestation')->createQuery('mp')
      ->andWhere('manifestation_id = ?',$values['manifestation_model']);
    $manifprices = $q->execute();
    
    $q = new Doctrine_Query();
    $q->from('PriceManifestation mp')
      ->whereIn('mp.manifestation_id',$values['manifestations_list'])
      ->delete()
      ->execute();
    
    foreach ( $values['manifestations_list'] as $manifid )
    {
      foreach ( $manifprices as $manifprice )
      {
        $manifprice = $manifprice->copy();
        $manifprice['id'] = null;
        $manifprice['created_at'] = date('Y-m-d H:i:s');
        $manifprice['updated_at'] = $manifprice['created_at'];
        $manifprice['manifestation_id'] = $manifid;
        $manifprice->save();
      }
    }
  }

  public function getModelName()
  {
    return 'Manifestation';
  }
}
