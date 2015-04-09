<?php

/**
 * Filter form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class FilterForm extends BaseFilterForm
{
  public function configure()
  {
  }
  
  public function setHidden()
  {
    foreach ( $this->widgetSchema->getFields() as $fieldName => $field )
      $this->widgetSchema[$fieldName] = new sfWidgetFormInputHidden;
    return $this;
  }
  public function setAutoDefaults(sfUser $user, $model)
  {
    $this->setDefaults(array(
      'filter'  => serialize($user->getAttribute(strtolower($model).'.filters', '', 'admin_module')),
      'type'    => strtolower($model),
    ));
    
    if ( sfContext::hasInstance() )
      $this->setDefault('sf_guard_user_id', sfContext::getInstance()->getUser()->getId());
    
    return $this;
  }
}
