<?php

/**
 * Place form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PlaceForm extends LocationForm
{
  /**
   * @see LocationForm
   */
  public function configure()
  {
    parent::configure();
  }
  
  public function doSave($con = NULL)
  {
    $this->values['place'] = true;
    $this->object->place = true;
    return parent::doSave($con);
  }
}
