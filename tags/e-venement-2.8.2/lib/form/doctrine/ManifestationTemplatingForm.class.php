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
    $this->widgetSchema   ['manifestation_model'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Manifestation',
      'url' => url_for('manifestation/ajax'),
      'config' => '{ max: '.sfConfig::get('app_manifestation_depends_on_limit',10).' }',
    ));
    $this->validatorSchema['manifestation_model'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
    ));
    
    // where to applicate it
    $this->widgetSchema   ['manifestations_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Manifestation',
      'url' => url_for('manifestation/ajax'), //?later=1'),
      'config' => '{ max: '.sfConfig::get('app_manifestation_depends_on_limit',10).' }',
    ));
    $this->validatorSchema['manifestations_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'multiple' => true,
    ));
    
    $this->widgetSchema   ['apply_to'] = new sfWidgetFormChoice(array(
      'choices' => $arr = array(
        'prices'        => 'Prices',
        'vat'           => 'VAT',
        'gauges'        => 'Gauges',
        'duration'      => 'Duration',
        'location'      => 'Location',
        'online_limit'  => 'Online limit',
        'color'         => 'Color',
      ),
      'multiple' => true,
    ));
    $this->validatorSchema['apply_to'] = new sfValidatorChoice(array(
      'choices' => array_keys($arr),
      'multiple' => true,
    ));
  }
  
  public function save($con = null)
  {
    $values = $this->getValues();
    
    $this->object = Doctrine::getTable('Manifestation')->createQuery('m', true)
      ->andWhere('m.id = ?', $values['manifestation_model'])
      ->fetchOne();
    $this->objects = Doctrine::getTable('Manifestation')->createQuery('m', true)
      ->andWhereIn('m.id', $values['manifestations_list'])
      ->execute();
    
    // direct properties
    foreach ( array('color', 'location', 'online_limit', 'duration', 'vat') as $prop )
    if ( in_array($prop, $values['apply_to']) )
    foreach ( $this->objects as $manif )
    {
      $manif->$prop = $this->object->$prop;
      $manif->save();
    }
    
    // gauges
    if ( in_array('gauges', $values['apply_to']) )
    foreach ( $this->objects as $manif )
    {
      $gauges = array();
      foreach ( $manif->Gauges as $gauge )
      {
        if ( $gauge->Tickets->count() == 0 )
          $gauge->delete();
        else
          $gauges[$gauge->workspace_id] = $gauge;
      }
      
      foreach ( $this->object->Gauges as $gauge )
      if ( !isset($gauges[$gauge->workspace_id]) )
      {
        $g = $gauge->copy();
        //$g->id = NULL;
        $manif->Gauges[] = $g;
      }
      else
      {
        foreach ( array_keys($gauge->getData()) as $field )
        if ( !in_array($field, array('id', 'manifestation_id')) )
          $gauges[$gauge->workspace_id]->$field = $gauge->$field;
        $gauges[$gauge->workspace_id]->save();
      }
      
      $manif->save();
    }
    
    // prices
    if ( in_array('prices', $values['apply_to']) )
    {
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
  }

  public function getModelName()
  {
    return 'Manifestation';
  }
}
