<?php

/**
 * location module configuration.
 *
 * @package    e-venement
 * @subpackage location
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: configuration.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class resourceGeneratorConfiguration extends baseResourceGeneratorConfiguration
{
  public function getFormClass()
  {
    return 'ResourceForm';
  }
}
