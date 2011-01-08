<?php

/**
 * Group form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class GroupForm extends BaseGroupForm
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
    
    $this->widgetSchema['contacts_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Contact',
      'url'   => url_for('contact/ajax'),
    ));
    
    $this->widgetSchema['professionals_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Professional',
      'url'   => url_for('professional/ajax'),
      'method'=> 'getFullName',
    ));
    
    $this->widgetSchema['professionals_list']->getJavascripts();
    $this->widgetSchema['professionals_list']->getStylesheets();
  }
}
