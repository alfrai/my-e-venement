<?php

/**
 * MemberCard form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MemberCardForm extends BaseMemberCardForm
{
  protected $noTimestampableUnset = true;

  public function configure()
  {
    unset($this->widgetSchema['updated_at']);
    unset($this->validatorSchema['updated_at']);
    
    $this->validatorSchema['sf_guard_user_id']->setOption('required',false);
  }
}
