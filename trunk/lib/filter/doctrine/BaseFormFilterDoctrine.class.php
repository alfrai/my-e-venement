<?php

/**
 * Project filter form base class.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterBaseTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class BaseFormFilterDoctrine extends sfFormFilterDoctrine
{
  protected $i18n_fields = array();
  
  public function setup()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url','CrossAppLink'));
    
    // I18N
    try
    {
      $i18n = Doctrine::getTable($this->getTable()->getTableName().'Translation')
        ->getColumns();
      unset($i18n['id'], $i18n['lang']);
      $this->i18n_fields = array_keys($i18n);
    }
    catch ( Doctrine_Exception $e ) {}
    
    foreach ( $this->i18n_fields as $field )
    {
      $this->widgetSchema   [$field] = new sfWidgetFormFilterInput(array('with_empty' => false));
      $this->validatorSchema[$field] = new sfValidatorPass(array('required' => false));
    }
    
    // HUGE FK
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
    
    // Enabling excluding ids in every filter form
    $this->validatorSchema['excluded_ids'] = new sfValidatorDoctrineChoice(array(
      'model' => 'WebOrigin',
      'multiple' => 'true',
      'required' => false,
    ));
    
    // TIMESTAMPABLE
    $this->resetDates();
  }
  
  protected function resetDates()
  {
    if (!( isset($this->noTimestampableUnset) && $this->noTimestampableUnset ))
    {
      unset($this->widgetSchema['created_at']);
      unset($this->widgetSchema['updated_at']);
      unset($this->widgetSchema['deleted_at']);
    }
    
    foreach ($this->widgetSchema->getFields() as $field)
    if ( $field instanceof sfWidgetFormFilterDate )
    {
      if ( class_exists('liWidgetFormJQueryDateText') )
      {
        $field->setOption('from_date', new liWidgetFormJQueryDateText(array(
          //'image'   => '/images/calendar_icon.png',
          'culture' => sfContext::getInstance()->getUser()->getCulture(),
          //'date_widget' => new sfWidgetFormI18nDate(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
        )));
        $field->setOption('to_date', new liWidgetFormJQueryDateText(array(
          //'image'   => '/images/calendar_icon.png',
          'culture' => sfContext::getInstance()->getUser()->getCulture(),
          //'date_widget' => new sfWidgetFormI18nDate(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
        )));
      }
      else
      {
        $field->setOption('from_date', new sfWidgetFormI18nDate(array(
          'culture' => sfContext::getInstance()->getUser()->getCulture(),
        )));
        $field->setOption('to_date', new sfWidgetFormI18nDate(array(
          'culture' => sfContext::getInstance()->getUser()->getCulture(),
        )));
      }
    }
  }
  
  public function getFields()
  {
    $i18n = array();
    foreach ( $this->i18n_fields as $field )
      $i18n[$field] = 'I18nText';
    
    return parent::getFields() + $i18n + array(
      'excluded_ids' => 'ExcludedIds',
    );
  }
  
  /**
    * I18N Query Part
    * Do not forget to add a function like this one for each I18N fields in your DoctrineFormFilters:
    *
    * function addNameColumnQuery(Doctrine_Query $query, $field, $values)
    * { addI18nTextQuery($q, $field, $values); }
    *
    **/
  public function addI18nTextQuery(Doctrine_Query $query, $field, $values)
  {
    return $this->addTextQuery($query, $field, $values, $query->getRootAlias().'.Translation');
  }
  
  public function addTextQuery(Doctrine_Query $query, $field, $values, $table = NULL)
  {
    $fieldName = $this->getFieldName($field);

    if (is_array($values) && isset($values['is_empty']) && $values['is_empty'])
    {
      $query->addWhere(sprintf('(%s.%s IS NULL OR %1$s.%2$s = ?)', $table ? $table : $query->getRootAlias(), $fieldName), array(''));
    }
    else if (is_array($values) && isset($values['text']) && '' != $values['text'])
    {
      $transliterate = sfConfig::get('software_internals_transliterate');
      $charset = sfConfig::get('software_internals_charset');
      $query->addWhere(
        sprintf("LOWER(translate(%s.%s,
          '%s',
          '%s')
        ) LIKE LOWER(?)", $table ? $table : $query->getRootAlias(), $fieldName, $transliterate['from'], $transliterate['to']),
        ''.iconv($charset['db'], $charset['ascii'], '%'.$values['text']).'%'
      );
    }
  }
  
  public function addExcludedIdsColumnQuery(Doctrine_Query $q, $field, $values)
  {
    if ( !$values )
      return $q;
    if ( !is_array($values) )
      $values = array($values);
    
    $a = $q->getRootAlias();
    $q->andWhereNotIn("$a.id", $values);
    
    return $q;
  }
}
