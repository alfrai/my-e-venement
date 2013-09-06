<?php

/**
 * Group filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class GroupFormFilter extends BaseGroupFormFilter
{
  public function configure()
  {
    $this->widgetSchema['sf_guard_user_id']->setOption('order_by',array('first_name, username',''));
  }
}
