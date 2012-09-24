<?php

/**
 * Professional filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProfessionalFormFilter extends BaseProfessionalFormFilter
{
  public function configure()
  {
    $this->widgetSchema['professional_type_id']->setOption('order_by',array('name',''));
  }
}
