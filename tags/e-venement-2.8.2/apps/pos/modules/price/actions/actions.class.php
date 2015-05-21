<?php

require_once dirname(__FILE__).'/../lib/priceGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/priceGeneratorHelper.class.php';

/**
 * price actions.
 *
 * @package    e-venement
 * @subpackage price
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class priceActions extends autoPriceActions
{
  /** differentiating filters from the event/price module **/
  protected function getFilters()
  {
    return $this->getUser()->getAttribute('pos.price.filters', $this->configuration->getFilterDefaults(), 'admin_module');
  }
  protected function setFilters(array $filters)
  {
    return $this->getUser()->setAttribute('pos.price.filters', $filters, 'admin_module');
  }
  protected function setPage($page)
  {
    $this->getUser()->setAttribute('pos.price.page', $page, 'admin_module');
  }

  protected function getPage()
  {
    return $this->getUser()->getAttribute('pos.price.page', 1, 'admin_module');
  }
  protected function getSort()
  {
    if (!is_null($sort = $this->getUser()->getAttribute('pos.price.sort', null, 'admin_module')))
    {
      return $sort;
    }

    $this->setSort($this->configuration->getDefaultSort());

    return $this->getUser()->getAttribute('pos.price.sort', null, 'admin_module');
  }

  protected function setSort(array $sort)
  {
    if (!is_null($sort[0]) && is_null($sort[1]))
    {
      $sort[1] = 'asc';
    }

    $this->getUser()->setAttribute('pos.price.sort', $sort, 'admin_module');
  }
}
