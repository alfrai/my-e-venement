<?php

/**
 * Base project form.
 * 
 * @package    e-venement
 * @subpackage form
 * @author     Your name here 
 * @version    SVN: $Id: BaseForm.class.php 20147 2009-07-13 11:46:57Z FabianLange $
 */
class BaseForm extends sfFormSymfony
{
  public function configure()
  {
  }
  
  public function renderFormTag($url, array $attributes = array())
  {
    if ( !isset($attributes['autocomplete']) )
      $attributes['autocomplete'] = 'off';
    return parent::renderFormTag($url,$attributes);
  }
}
