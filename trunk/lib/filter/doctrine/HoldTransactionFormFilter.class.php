<?php

/**
 * HoldTransaction filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class HoldTransactionFormFilter extends BaseHoldTransactionFormFilter
{
  public function configure()
  {
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputText;
  }
}
