<?php

/**
 * do actions.
 *
 * @package    e-venement
 * @subpackage do
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class doActions extends sfActions
{
  /**
    * confirm a client email address
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    * POST params:
    *   - json : a json array containing:
    *     . email: the client's email (required)
    *     . firstname: the client's firstname (required)
    *     . lastname: the client's lastname (family name) (required)
    *     . code: a string constructed as email.firstname.lastname.password.salt to be verified (required)
    * Returns :
    *   - HTTP return code
    *     . 500 if there was a problem processing the demand
    *     . 403 if authentication as a valid webservice has failed
    *     . 404 if there is no such account in database corresponding to the given criterias
    *     . 412 if all the required arguments have not been sent
    *     . 201 if all went good and the account activation has worked
    *   - json: if necessary (new password), this is returned in a JSON way
    *
    **/
  public function executeConfirm(sfWebRequest $request)
  {
    return require('confirm.php');
  }
  
  /**
    * creates or updates a contact file
    * updates a transaction with a given contact
    * only available if $config['vel']['resa-noid'] is set
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    * POST params: a var "json" containing this kind of json content
    *   - user: a json array describing the contact/user (see in the code for sample)
    *   - transaction: the transaction id concerned
    * Returns :
    *   - HTTP return code
    *     . 200 if the transaction was well updated
    *     . 403 if authentication as a valid webservice has failed
    *     . 406 if the input POST content doesn't embed the required values
    *     . 412 if the input user's json content doesn't pass its checksum
    *     . 500 if there was a problem processing the demand
    *
    **/
  public function executeContactUpdate(sfWebRequest $request)
  {
    return require('contact-update.php');
  }
  
  /**
    * records the payment done "online"
    * don't forget the HTTP session given after identifying the client, and containing the t
ransaction id
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    *   - paid: a string of the amount paid and validated (required)
    * Returns :
    *   - HTTP return code
    *     . 200 if payment has been well recorded and all has been paid
    *     . 202 if payment has been well recorded but it doesn't recover all the "debt"
    *     . 403 if authentication as a valid webservice has failed
    *     . 406 if the payment argument is not given
    *     . 410 if no transactionthe payment argument is not given
    *     . 500 if there was a problem processing the demand, including with upgrading the transaction to a pre-reservation state
    *     . 502 if there was a problem recording the raw informations coming from the bank
    *
    **/
  public function executePayment(sfWebRequest $request)
  {
    return require('payment.php');
  }
  
  
  /**
    * initiates the transaction, prereserving the tickets before paiement
    * don't forget the HTTP session given after identifying the client
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    * POST params: a var "json" containing this kind of json content
    *   - json: a json array describing the command (see in the code for sample)
    * Returns :
    *   - HTTP return code
    *     . 201 if tickets have been well pre-reserved
    *     . 403 if authentication as a valid webservice has failed
    *     . 406 if the input json content doesn't embed the required values
    *     . 412 if the input json array is not conform with its checksum
    *     . 500 if there was a problem processing the demand
    *   - a json array containing :
    *     . manifestations quoted for updates
    *     . required amount to pay
    *     . transaction id to give back for paiement and final reservation
    *
    **/
  public function executeBook(sfWebRequest $request)
  {
    return require('book.php');
  }
  
  
  /**
    * pre-login as a client
    * (distinct from the real "authentication" of the distant system, that's why we told it 'identification'
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    *   - email: the client's email (required)
    *   - name: the client's name (required)
    *   - passwd: the client md5(passwd) (already encrypted)
    *   - request: just set this GET var to send a new password to the client's email if existing in database
    * Returns :
    *   - HTTP return code
    *     . 500 if there was a problem processing the demand
    *     . 403 if passwd/email are invalid or if authentication as a valid webservice has failed
    *     . 404 if there is no such email in database when asked for a new password
    *     . 406 if a password was given but long enough (> 4 chars)
    *     . 412 if all the required arguments have not been sent
    *     . 201 if a new password has been created
    *     . 202 if all is ok, the authentication worked
    *   - json: if necessary (new password), this is returned in a JSON way
    *
    **/
  public function executeAuthentication(sfWebRequest $request)
  {
    return require('authentication.php');
  }

  protected function authenticate(sfWebRequest $request)
  {
    return wsConfiguration::authenticate($request);
  }

  protected function getWhatToPay()
  {
    $q = Doctrine::getTable('Transaction')->createQuery('t')
      ->select('t.id, sum(tck.value) AS topay')
      ->andWhere('t.id = ?',$this->getUser()->getAttribute('transaction_id'))
      ->andWhere('tck.cancelling IS NULL AND tck.duplicating IS NULL')
      ->groupBy('t.id');
    return $q->fetchOne()->topay;
  }
}
