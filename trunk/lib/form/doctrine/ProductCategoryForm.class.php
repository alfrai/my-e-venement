<?php

/**
 * ProductCategory form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProductCategoryForm extends BaseProductCategoryForm
{
  public function configure()
  {
    $this->widgetSchema['vat_id']->setOption('order_by', array('value',''));
  }
}
