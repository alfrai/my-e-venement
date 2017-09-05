<?php

/**
 * default actions.
 *
 * @package    e-venement
 * @subpackage kiosk
 * @author     Romain SANCHEZ <romain.sanchez AT libre-informatique.fr>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class defaultActions extends sfActions
{
  /**
  * Execute index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    return 'Success';	
  }

  /**
  * Access public kiosk
  *
  * @param sfRequest $request A request object
  */
  public function executePublic(sfWebRequest $request)
  {
    if( !sfContext::getInstance()->getUser()->getId() )
      $this->dispatcher->notify(new sfEvent($this, 'kiosk.init'));
  }

  /**
  * Change culture
  *
  * @param sfRequest $request A request object
  */
  public function executeCulture(sfWebRequest $request)
  {
    $cultures = array_keys(sfConfig::get('project_internals_cultures', array('fr' => 'Français')));
    
    // culture defined explicitly
    if ( $request->hasParameter('lang') && in_array($request->getParameter('lang'), $cultures) )
    {
      $this->getUser()->setCulture($request->getParameter('lang'));
      $this->getUser()->setAttribute('global_culture_forced', true);
    }
    
    if ( !$this->getUser()->getAttribute('global_culture_forced', false) )
    {
      // all browser languages
      $user_langs = array();
      foreach ( $request->getLanguages() as $lang )
        if ( !isset($user_lang[substr($lang, 0, 2)]) )
          $user_langs[substr($lang, 0, 2)] = $lang;
      
      // comparing to the supported languages
      $done = false;
      foreach ( $user_langs as $culture => $lang )
        if ( in_array($culture, $cultures) )
        {
          $done = $culture;
          $this->getUser()->setCulture($culture);

          break;
        }
      
      // culture by default
      if ( !$done )
        $this->getUser()->setCulture($cultures[0]);
    }
      
    $this->redirect($request->getReferer());
  }

  /**
  * Get country list
  *
  * @param sfRequest $request A request object
  */
  public function executeGetCountries(sfWebRequest $request)
  {
    $countryService = sfContext::getInstance()->getContainer()->get('app_country_service');

    $this->countries = $countryService->getAllCountries($request->getParameter('culture'));
  }

  /**
  * Persist transaction payment receipts
  *
  * @param sfRequest $request A request object
  */
  public function executeSavePaymentRecord(sfWebRequest $request)
  {
    $record = new EptRecord();

    $record->transaction_id = $request->getParameter('transaction');
    $record->client_receipt = base64_encode($request->getParameter('clientReceipt'));

    if( $request->getParameter('sellerReceipt') )
      $record->seller_receipt = base64_encode($request->getParameter('sellerReceipt'));

    $record->save();

    $this->getResponse()->setHttpHeader('Content-type','application/json');

    // Evil
    die('ok');

    return sfView::NONE;
  }
}
