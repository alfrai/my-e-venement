<?php

/**
 * phone_type module helper.
 *
 * @package    e-venement
 * @subpackage phone_type
 * @author     Your name here
 * @version    SVN: $Id: helper.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class phone_typeGeneratorHelper extends BasePhone_typeGeneratorHelper
{
  public function linkToExtraAction($params)
  {
    if (!key_exists('ui-icon', $params)) $params['ui-icon'] = '';
    $params['params'] = UIHelper::addClasses($params, '');
    $params['ui-icon'] = $this->getIcon($params['extra-icon'], $params);
    return '<li class="sf_admin_action_'.$params['action'].'">'.link_to(UIHelper::addIcon($params) . __($params['label']), sfContext::getInstance()->getModuleName().'/'.$params['action'], $params['params']).'</li>';
  }
}
