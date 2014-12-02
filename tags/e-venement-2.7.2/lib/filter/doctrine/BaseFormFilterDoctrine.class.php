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
  public function setup()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url','CrossAppLink'));

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
    $this->resetDates();
  }
  
  protected function resetDates()
  {
    if ( !isset($this->noTimestampableUnset) || !$this->noTimestampableUnset )
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
  
  public function addTextQuery(Doctrine_Query $query, $field, $values)
  {
    $fieldName = $this->getFieldName($field);

    if (is_array($values) && isset($values['is_empty']) && $values['is_empty'])
    {
      $query->addWhere(sprintf('(%s.%s IS NULL OR %1$s.%2$s = ?)', $query->getRootAlias(), $fieldName), array(''));
    }
    else if (is_array($values) && isset($values['text']) && '' != $values['text'])
    {
      $transliterate = sfConfig::get('software_internals_transliterate');
      $charset = sfConfig::get('software_internals_charset');
      $query->addWhere(
        sprintf("LOWER(translate(%s.%s,
          '%s',
          '%s')
        ) LIKE LOWER(?)", $query->getRootAlias(), $fieldName, $transliterate['from'], $transliterate['to']),
        ''.iconv($charset['db'], $charset['ascii'], '%'.$values['text']).'%'
      );
    }
  }
}
