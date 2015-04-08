<?php

/**
 * Phonenumber form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PhonenumberForm extends BasePhonenumberForm
{
  public function configure()
  {
    $this->widgetSchema['name']     = new liWidgetFormDoctrineJQueryAutocompleterGuide(array(
      'model' => 'PhoneType',
      'url'   => url_for('phone_type/ajax'),
      'method_for_query' => 'findOneByName',
    ));
    $this->widgetSchema['name']->getStylesheets();
    $this->widgetSchema['name']->getJavascripts();
    
    parent::configure();
  }
}
