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
    unset($this->widgetSchema['prices_list']);
    
    $this->widgetSchema['manifestation_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Manifestation',
      'url' => url_for('manifestation/ajax'),
      'config' => '{ max: '.sfConfig::get('app_manifestation_depends_on_limit',10).' }',
    ));
    
    $q = Doctrine::getTable('Workspace')->createQuery('ws');
    if ( sfContext::hasInstance() )
      $q->leftJoin('ws.Users u')
        ->andWhere('u.id = ?',sfContext::getInstance()->getUser()->getId());
    $this->widgetSchema['workspace_id']->setOption('add_empty',true)
      ->setOption('order_by',array('name',''))
      ->setOption('query',$q);
    $this->validatorSchema['workspace_id']
      ->setOption('query',$q);
    
    $this->widgetSchema['manifestation_id'] = new sfWidgetFormInputHidden;
    
    $this->validatorSchema['value']->setOption('required',false);
  }
  public function setHidden($hides = array('manifestation_id','workspace_id'))
  {
    foreach ( $hides as $hide )
    if ( isset($this->widgetSchema[$hide]) )
      $this->widgetSchema[$hide] = new sfWidgetFormInputHidden();
    return $this;
  }
  
  public function setUpdateOnly($only = array())
  {
    if ( !$only )
    {
      if ( isset($this->widgetSchema['only']) )
        unset($this->widgetSchema['only']);
      return $this;
    }
    
    if ( !is_array($only) )
      $only = array($only);
    
    // if "only" is set, only update the given fields (comma separated)
    $this->widgetSchema['only'] = new sfWidgetFormInputHidden;
    $this->validatorSchema['only'] = new sfValidatorPass;
    $this->setDefault('only', implode(',', $only));
  }
  
  protected function doBind(array $values)
  {
    if (!( isset($values['only']) && $values['only'] ))
      return parent::doBind($values);
    
    // if "only" is set, only update the given fields (comma separated)
    $only = $values['only'];
    unset($values['only']);
    
    foreach ( $values as $field => $value )
    if ( !in_array($field, $tmp = explode(',', $only))
      && in_array($field, array_keys($this->object->getTable()->getColumns())) )
      $values[$field] = $this->object->$field;
    
    parent::doBind($values);
  }
}
