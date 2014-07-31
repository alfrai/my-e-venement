<?php

/**
 * SurveyAnswer filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SurveyAnswerFormFilter extends BaseSurveyAnswerFormFilter
{
  /**
   * @see TraceableFormFilter
   */
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink', 'Url'));
    parent::configure();
    
    $this->widgetSchema   ['survey_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Survey',
      'order_by' => array('s.name, st.description', ''),
      'add_empty' => true,
    ));
    $this->validatorSchema['survey_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Survey',
      'required' => false,
    ));
    $this->widgetSchema   ['contact_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Contact',
      'url'   => cross_app_url_for('rp','contact/ajax'),
    ));
    $this->validatorSchema['contact_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Contact',
      'required' => false,
    ));
    $this->widgetSchema   ['survey_query_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'SurveyQuery',
      'url'   => url_for('query/ajax'),
    ));
    $this->validatorSchema['survey_query_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'SurveyQuery',
      'required' => false,
    ));
    $this->widgetSchema   ['apply_to_manifestation_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Manifestation',
      'url'   => cross_app_url_for('event', 'manifestation/ajax'),
    ));
    $this->validatorSchema['apply_to_manifestation_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'required' => false,
    ));
  }
  
  public function getFields()
  {
    return parent::getFields() + array(
      'survey_id' => 'SurveyId',
      'contact_id' => 'ContactId',
      'apply_to_manifestation_id' => 'ApplyToManifestationId',
    );
  }
  
  public function addSurveyIdColumnQuery(Doctrine_Query $q, $field, $values)
  {
    if ( $values )
      $q->andWhereIn('s.id', $values);
    return $q;
  }
  public function addContactIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value )
      $q->andWhere('g.contact_id = ?', $value);
    return $q;
  }
  public function addApplyToManifestationIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value )
      $q->andWhere('at.manifestation_id = ?', $value);
    return $q;
  }
}
