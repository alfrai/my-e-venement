<?php

require_once dirname(__FILE__).'/../lib/entry_ticketsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/entry_ticketsGeneratorHelper.class.php';

/**
 * entry_tickets actions.
 *
 * @package    e-venement
 * @subpackage entry_tickets
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class entry_ticketsActions extends autoEntry_ticketsActions
{
  public function executeDel(sfWebRequest $request)
  {
    $this->getRoute()->getObject()->delete();
    return $this->redirect('@entry_tickets');
  }
}
