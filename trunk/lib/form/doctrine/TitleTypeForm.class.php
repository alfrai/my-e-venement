<?php

/**
 * TitleType form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TitleTypeForm extends BaseTitleTypeForm
{
  public function configure()
  {
    $this->validatorSchema['type']->setOption('required', false);
  }
  public function preSave()
  {
    parent::preSave();
    $this->type = 'title';
  }
}
