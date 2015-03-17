<?php

/**
 * sfGuardPermission form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrinePluginFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class sfGuardPermissionForm extends PluginsfGuardPermissionForm
{
  public function configure()
  {
    $this->widgetSchema['users_list'] ->setOption('order_by',array('first_name, last_name, username',''));
    $this->widgetSchema['groups_list']->setOption('order_by',array('name',''));
  }
}
