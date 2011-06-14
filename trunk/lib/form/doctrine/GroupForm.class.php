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
      'order_by' => array('name,firstname',''),
    ));
    
    $this->widgetSchema['professionals_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Professional',
      'url'   => url_for('professional/ajax'),
      'method'=> 'getFullName',
      'order_by' => array('c.name,c.firstname,o.name,t.name,p.name',''),
    ));
    $this->widgetSchema['professionals_list']->getJavascripts();
    $this->widgetSchema['professionals_list']->getStylesheets();
    
    // the group's owner
    $sf_user = sfContext::getInstance()->getUser();
    $this->validatorSchema['sf_guard_user_id'] = new sfValidatorInteger(array(
      'min' => $sf_user->getId(),
      'max' => $sf_user->getId(),
      'required' => true,
    ));
    $choices = array();
    if ( $sf_user->hasCredential('pr-group-common') )
    {
      $this->validatorSchema['sf_guard_user_id']->setOption('required',false);
      $choices[''] = '';
    }
    $choices[$sf_user->getId()] = $sf_user;
    $this->widgetSchema   ['sf_guard_user_id'] = new sfWidgetFormChoice(array(
      'choices'   => $choices,
      'default'   => $this->isNew() ? $sf_user->getId() : $this->getObject()->sf_guard_user_id,
    ));
  }
}
