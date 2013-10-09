<?php

/**
 * Location form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ResourceForm extends LocationForm
{
  public function doSave($con = NULL)
  {
    $this->values['place'] = false;
    $this->object->place = false;
    return parent::doSave($con);
  }
}
