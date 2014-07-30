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
    $this->widgetSchema   ['contact_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Contact',
      'url'   => cross_app_url_for('rp','contact/ajax'),
    ));
    $this->widgetSchema   ['survey_query_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'SurveyQuery',
      'url'   => url_for('query/ajax'),
    ));
  }
}
