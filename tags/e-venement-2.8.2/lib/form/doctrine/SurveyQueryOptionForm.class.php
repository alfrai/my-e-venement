<?php

/**
 * SurveyQueryOption form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SurveyQueryOptionForm extends BaseSurveyQueryOptionForm
{
  public function configure()
  {
    $this->widgetSchema['survey_query_id'] = new sfWidgetFormInputHidden;
    $this->useFields(array_merge(
      array_keys(sfConfig::get('project_internals_cultures', array('fr' => 'Français'))),
      array('value')
    ));
  }
}
