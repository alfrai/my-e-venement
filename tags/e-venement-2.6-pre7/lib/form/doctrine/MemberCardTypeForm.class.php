<?php

/**
 * MemberCardType form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MemberCardTypeForm extends BaseMemberCardTypeForm
{
  public function configure()
  {
    $this->widgetSchema['users_list']
      ->setOption('expanded',true)
      ->setOption('order_by',array('username',''));
  }
}
