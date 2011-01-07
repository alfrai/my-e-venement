<?php

/**
 * Project form base class.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormBaseTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class BaseFormDoctrine extends sfFormDoctrine
{
  public function setup()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
    
    unset($this->validatorSchema['created_at']);
    unset($this->validatorSchema['updated_at']);
    
    if ( isset($this->widgetSchema['contact_id']) )
    $this->widgetSchema['contact_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Contact',
      'url'   => url_for('contact/ajax'),
    ));
    if ( isset($this->widgetSchema['organism_id']) )
    $this->widgetSchema['organism_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Organism',
      'url'   => url_for('organism/ajax'),
    ));
    
    if ( isset($this->widgetSchema['groups_list']) )
    $this->widgetSchema['groups_list']->setOption('renderer_class','sfWidgetFormSelectDoubleList');
  }
}
