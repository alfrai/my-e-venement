<?php

/**
 * search actions.
 *
 * @package    e-venement
 * @subpackage search
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class searchActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new SearchForm();
    
    $this->search_fields = array(
      'c.name'        => 'Name',
      'c.firstname'   => 'Firstname',
      'o.id'          => 'Organism',
      'oc.id'         => 'Category',
      'g.id'          => 'Group',
      'c.postalcode'  => 'Postalcode',
      'c.city'        => 'City',
      'c.description' => 'Description',
    );
  }
}
