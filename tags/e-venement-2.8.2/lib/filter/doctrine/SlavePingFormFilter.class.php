<?php

/**
 * SlavePing filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SlavePingFormFilter extends BaseSlavePingFormFilter
{
  protected $noTimestampableUnset = true;
  public function configure()
  {
    $this->widgetSchema['created_at']->setOption('with_empty', false);
  }
}
