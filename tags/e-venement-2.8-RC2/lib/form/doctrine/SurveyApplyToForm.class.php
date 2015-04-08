<?php

/**
 * SurveyApplyTo form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SurveyApplyToForm extends BaseSurveyApplyToForm
{
  public function configure()
  {
    $this->widgetSchema   ['survey_id'] = new sfWidgetFormInputHidden;
    $this->widgetSchema   ['manifestation_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Manifestation',
      'url' => cross_app_url_for('event', 'manifestation/ajax'),
      'method_for_query' => 'slightlyFindOneById',
    ));
    $this->widgetSchema   ['professional_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Professional',
      'url' => cross_app_url_for('event', 'professional/ajax'),
    ));
    
    $this->useFields(array(
      'everywhere',
      'manifestation_id',
      'contact_id',
      //'professional_id', // not yet available
      //'organism_id',     // not yet available
      'group_id',
      'date_from',
      'date_to',
    ));
  }
}
