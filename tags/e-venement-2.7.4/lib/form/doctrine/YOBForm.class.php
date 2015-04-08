<?php

/**
 * YOB form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class YOBForm extends BaseYOBForm
{
  public function configure()
  {
    $this->validatorSchema['year']->setOption('required',false);
    $this->useFields(array('day','month','year','name'));
    
    $this->widgetSchema['name']->setLabel('Firstname');
  }
}
