<?php

/**
 * debts module configuration.
 *
 * @package    symfony
 * @subpackage debts
 * @author     Your name here
 * @version    SVN: $Id: configuration.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class debtsGeneratorConfiguration extends BaseDebtsGeneratorConfiguration
{
  public function getFilterDefaults()
  {
    return array('all' => false);
  }
}
