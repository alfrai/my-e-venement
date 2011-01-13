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
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
    
    unset($this->widgetSchema['created_at']);
    unset($this->widgetSchema['updated_at']);
    unset($this->widgetSchema['deleted_at']);
    
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
