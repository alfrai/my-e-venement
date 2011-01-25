<?php

/**
 * Project form base class.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormBaseTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class BaseFormDoctrine extends sfFormDoctrine
{
  public function setup()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink','Url'));
    
    if ( isset($this->widgetSchema['contact_id']) )
    $this->widgetSchema['contact_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Contact',
      'url'   => cross_app_url_for('rp','contact/ajax'),
    ));
    if ( isset($this->widgetSchema['organism_id']) )
    $this->widgetSchema['organism_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Organism',
      'url'   => cross_app_url_for('rp','organism/ajax'),
    ));
    
    if ( isset($this->widgetSchema['groups_list']) )
    $this->widgetSchema['groups_list']->setOption('renderer_class','sfWidgetFormSelectDoubleList');
    
    $this->resetDates();
  }
  
  protected function resetDates()
  {
    unset($this->widgetSchema['created_at']);
    unset($this->widgetSchema['updated_at']);
    unset($this->widgetSchema['deleted_at']);
    unset($this->validatorSchema['created_at']);
    unset($this->validatorSchema['updated_at']);
    unset($this->validatorSchema['deleted_at']);
    
    foreach ( $this->widgetSchema->getFields() as $name => $field )
    if ( ($field instanceof sfWidgetFormDate || $field instanceof sfWidgetFormDateTime) && class_exists('sfWidgetFormJQueryDate') )
    {
      $this->widgetSchema[$name] = new liWidgetFormDateTime(array(
        'date' => new liWidgetFormJQueryDateText(),
        'time' => new liWidgetFormTimeText(),
      ));
    }
  }
  
  public function addTextQuery(Doctrine_Query $query, $field, $values)
  {
    $fieldName = $this->getFieldName($field);

    if (is_array($values) && isset($values['is_empty']) && $values['is_empty'])
    {
      $query->addWhere(sprintf('(%s.%s IS NULL OR %1$s.%2$s = ?)', $query->getRootAlias(), $fieldName), array(''));
    }
    else if (is_array($values) && isset($values['text']) && '' != $values['text'])
    {
      $query->addWhere(
        sprintf("LOWER(translate(%s.%s,
          '%s',
          '%s')
        ) LIKE LOWER(?)", $query->getRootAlias(), $fieldName, sfContext::getInstance()->getConfiguration()->transliterate[0], sfContext::getInstance()->getConfiguration()->transliterate[1]),
        '%'.iconv('UTF-8', 'ASCII//TRANSLIT', $values['text']).'%'
      );
    }
  }
}
