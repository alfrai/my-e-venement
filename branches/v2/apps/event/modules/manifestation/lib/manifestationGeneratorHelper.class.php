<?php

/**
 * manifestation module helper.
 *
 * @package    e-venement
 * @subpackage manifestation
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: helper.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class manifestationGeneratorHelper extends BaseManifestationGeneratorHelper
{
  public function linkToList($params, $object = null)
  {
    $params['ui-icon'] = $this->getIcon('list', $params);
    return '<li class="sf_admin_action_list">'.link_to(UIHelper::addIcon($params) . __($params['label'], array(), 'sf_admin'), is_object($object)
      ? 'event/show?id='.$object->event_id
      : '@manifestation', $params['params']).'</li>';
  }
}
