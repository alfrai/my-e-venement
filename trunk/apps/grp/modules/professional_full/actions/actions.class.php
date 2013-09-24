<?php

require_once dirname(__FILE__).'/../lib/professional_fullGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/professional_fullGeneratorHelper.class.php';

/**
 * professional_full actions.
 *
 * @package    e-venement
 * @subpackage professional_full
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class professional_fullActions extends autoProfessional_fullActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->redirect('professional/index');
  }
  
  public function executeUpdate(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  public function executeNew(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  public function executeCreate(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  public function executeDelete(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  public function executeBatchDelete(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
}
