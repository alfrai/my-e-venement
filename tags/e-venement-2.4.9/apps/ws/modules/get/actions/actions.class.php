<?php

/**
 * get actions.
 *
 * @package    e-venement
 * @subpackage get
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class getActions extends sfActions
{
  /**
    * 
    * Returns :
    *   - HTTP status
    *     . 200: all is processed normally
    *     . 403: authentication failed
    *     . 500: internal error
    *   - content
    *     . nothing: error
    *     . json: returns a json array describing all the necessary information
    *
    **/
  public function executeInfos(sfWebRequest $request)
  {
    return require('infos.php');
  }
  
  public function executeTransaction(sfWebRequest $request)
  {
    return require('transaction.php');
  }
  
  protected function authenticate(sfWebRequest $request)
  {
    return wsConfiguration::authenticate($request);
  }
}
