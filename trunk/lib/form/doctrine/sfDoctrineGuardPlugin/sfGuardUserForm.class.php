<?php

/**
 * sfGuardUser form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrinePluginFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class sfGuardUserForm extends PluginsfGuardUserForm
{
  public function configure()
  {
    $this->widgetSchema['permissions_list']->setOption('order_by',array('name',''));
    $this->widgetSchema['groups_list']->setOption('order_by',array('name',''));
  }
}
