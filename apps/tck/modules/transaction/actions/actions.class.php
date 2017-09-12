<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

use Knp\Snappy\Pdf;

require_once dirname(__FILE__).'/../lib/transactionGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/transactionGeneratorHelper.class.php';

/**
 * transaction actions.
 *
 * @package    e-venement
 * @subpackage transaction
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class transactionActions extends autoTransactionActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->redirect('transactionsList/index');
  }
  public function executeDispatch(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('I18N'));

    $dispatch = $request->getParameter('dispatch');
    $q = Doctrine::getTable('Ticket')->createQuery('tck')
      ->andWhereIn('tck.id', $dispatch)
      ->leftJoin('tck.Transaction t')
      ->leftJoin('tck.Duplicatas dup')
      ->leftJoin('tck.Cancelling c')
      ->andWhere('tck.cancelling IS NULL AND c.id IS NULL')
      ->andWhere('tck.duplicating IS NULL AND dup.id IS NULL')
    ;
    $this->transaction = new Transaction;
    $tickets = $q->execute();

    if ( $tickets->count() == 0 )
    {
      $this->getUser()->setFlash('error', __('No ticket to be dispatched...'));
      $this->redirect($request->getReferer());
    }

    foreach ( $tickets as $ticket )
    {
      $ticket->Transaction->closed = false;
      if ( $ticket->Transaction->isModified() )
        $ticket->Transaction->save();
      $this->transaction->Tickets[] = $ticket;
    }
    $this->transaction->save();

    $this->getUser()->setFlash('notice', __('%%nb%% ticket(s) have been dispatched into this new transaction.', array('%%nb%%' => $tickets->count())));
    $this->redirect('transaction/edit?id='.$this->transaction->id);
  }
  public function executeFind(sfWebRequest $request)
  {
    // find by ticket_id
    if ( $request->hasParameter('ticket_id') )
    {
      $q = Doctrine::getTable('Ticket')->createQuery('tck')
        ->andWhere('tck.id = ?', $request->getParameter('ticket_id'));
      $this->forward404Unless($ticket = $q->fetchOne());
      if ( $ticket->cancelling )
        $this->redirect('ticket/pay?id='.$ticket->transaction_id);
      else
        $this->redirect('transaction/edit?id='.$ticket->transaction_id);
    }

    // find by seat_id + gauge_id
    if ( $request->hasParameter('seat_id') && ($request->hasParameter('gauge_id')||$request->hasParameter('manifestation_id')) )
    {
      $this->getContext()->getConfiguration()->loadHelpers(array('I18N'));

      $q = Doctrine::getTable('Seat')->createQuery('s')
        ->andWhere('s.id = ?', $request->getParameter('seat_id'));
      if ( $request->hasParameter('gauge_id') )
        $q->leftJoin('s.Tickets tck WITH tck.gauge_id = ?', $request->getParameter('gauge_id'));
      if ( $request->hasParameter('manifestation_id') )
        $q->leftJoin('s.Tickets tck WITH tck.manifestation_id = ?', $request->getParameter('manifestation_id'));

      $this->forward404Unless($seat = $q->fetchOne());
      if ( $seat->Tickets->count() > 0 )
        $this->redirect('transaction/edit?id='.$seat->Tickets[0]->transaction_id);

      $q = Doctrine::getTable('Gauge')->createQuery('g',false);
      if ( $request->hasParameter('gauge_id') )
        $q->andWhere('g.id = ?', $request->getParameter('gauge_id'));
      if ( $request->hasParameter('manifestation_id') )
        $q->andWhere('g.manifestation_id = ?', $request->getParameter('manifestation_id'));
      $q->leftJoin('g.Manifestation m')
        ->leftJoin('m.Location l')
        ->leftJoin('l.SeatedPlans sp')
        ->leftJoin('sp.Workspaces spw')
        ->andWhere('g.workspace_id = spw.id')
        ->leftJoin('sp.Seats sps')
        ->andWhere('sps.id = ?',$request->getParameter('seat_id'))
      ;
      $this->forward404Unless($gauge = $q->fetchOne());

      $this->transaction = new Transaction;
      $ticket = new Ticket;
      $ticket->Seat = $seat;
      $ticket->Gauge = $gauge;
      $ticket->price_name = sfConfig::get('app_tickets_wip_price', 'WIP');
      $ticket->value = 0;
      $this->transaction->Tickets[] = $ticket;
      $this->transaction->save();

      $this->getUser()->setFlash('success', __('Transaction created'));
      $this->redirect('transaction/edit?id='.$this->transaction->id);
    }

    $this->forward404();
  }
  public function executeRegistered(sfWebRequest $request)
  {
    $this->transaction = $this->getRoute()->getObject();
    $this->forms = array();

    foreach ( $this->transaction->Tickets as $ticket )
    if ( $ticket->gauge_id == $request->getParameter('gauge_id', 0)
      && $ticket->price_id == $request->getParameter('price_id', 0)
      && $ticket->Duplicatas->count() == 0
      && !$ticket->cancelling )
      $tickets[$ticket->id] = $ticket;

    asort($tickets); // sorting tickets to keep the same order anytime
    foreach ( $tickets as $ticket )
    {
      $form = new TicketRegisteredForm($ticket);

      // include unconfirmed contacts in autocomplete results
      $url = $form->getWidget('contact_id')->getOption('url');
      $form->getWidget('contact_id')->setOption('url', $url . '?unconfirmed=1');

      $this->forms[] = $form;
    }
  }
  public function executeRegister(sfWebRequest $request)
  {
    $id = $request->getParameter('id');
    $data = $request->getParameter('ticket');
    if ( !isset($data[$id]) )
      return 'Error';

    $this->form = new TicketRegisteredForm(null,  array('unconfirmed' => true));

    if ( !$this->getUser()->hasCredential('tck-transaction-reduc') && isset($data[$id]['reduc']) )
      unset($data[$id]['reduc']);
    $this->form->bind($data[$id]);

    if ( $request->hasParameter('debug') && sfConfig::get('sf_web_debug', false) )
    {
      $this->debug = true;
      $this->getResponse()->setContentType('text/html');
      $this->getResponse()->sendHttpHeaders();
      $this->setLayout('layout');
    }

    if ( !$this->form->isValid() ) {

    $errors = array();
    foreach ($this->form->getErrorSchema() as $key => $err) {
      if ($key) $errors[$key] = $err->getMessage();
      }
      print_r($errors);
      die('not valid');
      return 'Error';
    }

    $this->getContext()->getConfiguration()->loadHelpers(array('Url', 'I18N'));

    $ticket = $this->form->save();
    $this->json = array('success' => array(
      'message'   => __('Ticket #%%id%% registered', array('%%id%%' => $ticket->id)),
      'url_back' => url_for('transaction/registered'
        .'?id='.$ticket->transaction_id
        .'&price_id='.$ticket->price_id
        .'&gauge_id='.$ticket->gauge_id
      ),
    ));
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
    parent::executeEdit($request);

    $this->dispatcher->notify(new sfEvent($this, 'tck.before_transaction_respawning', array('transaction' => $this->transaction)));
    $this->transaction->save();

    if ( $this->transaction->closed && $this->getUser()->hasCredential('tck-unblock') && sfConfig::get('app_transaction_auto_reopen', false) )
    {
      $this->transaction->closed = false;
      $this->transaction->save();
      $this->getUser()->setFlash('success', __('The transaction has been reopened automatically.'));
    }
    elseif ( $this->transaction->closed )
    {
      $this->getUser()->setFlash('error', __('You have to re-open the transaction before accessing it'));
      $this->redirect('transaction/respawn?id='.$this->transaction->id);
    }
    if ( $this->transaction->type == 'cancellation' )
    {
      $this->getUser()->setFlash('error',__("You can respawn here only normal transactions"));
      $this->redirect('ticket/pay?id='.$this->transaction->id);
    }

    $this->form = array();

    // Contact
    $this->form['contact_id'] = new sfForm;
    $this->form['contact_id']->setDefault('contact_id', $this->transaction->contact_id);
    $ws = $this->form['contact_id']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['contact_id']->getValidatorSchema();
    $ws['contact_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Contact',
      'url'   => cross_app_url_for('rp', 'contact/ajax'),
      'config' => '{ max: 50 }',
    ));
    $vs['contact_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Contact',
      'required' => false,
    ));

    // Professional
    $this->form['professional_id'] = new sfForm;
    $ws = $this->form['professional_id']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['professional_id']->getValidatorSchema();
    $this->form['professional_id']->setDefault('professional_id', $this->transaction->professional_id);
    $ws = $this->form['professional_id']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['professional_id']->getValidatorSchema();
    $ws['professional_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Professional',
      'add_empty' => true,
      'query' => Doctrine::getTable('Professional')->createQuery('p')->andWhere('c.id = ?',$this->transaction->contact_id),
      'method' => 'getFullDesc',
    ));
    $vs['professional_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Professional',
      'required' => false,
    ));

    $this->form['postalcode'] = new sfForm;
    $ws = $this->form['postalcode']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['postalcode']->getValidatorSchema();
    $this->form['postalcode']->setDefault('postalcode', $this->transaction->postalcode);
    $ws['postalcode'] = new sfWidgetFormInputText(array(), array('size' => 5));
    $vs['postalcode'] = new sfValidatorString(array(
      'required' => false,
      'max_length' => 32,
    ));

    $this->form['country'] = new sfForm;
    $ws = $this->form['country']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['country']->getValidatorSchema();
    $this->form['country']->setDefault('country', $this->transaction->country);  
    $ws['country'] = new sfWidgetFormDoctrineChoice(array(
      'query' => Doctrine::getTable('Country')->createQuery('c')
        ->leftJoin("c.Translation ct WITH ct.lang = ?", sfContext::getInstance()->getUser()->getCulture())
        ->orderBy('ct.name'),
      'model' => 'Country', 
      'add_empty' => true,
      'key_method' => 'getName'
    ));
    $vs['country'] = new sfValidatorString(array(
      'required' => false
    ));
    

    // Deposit + Shipment (calling the "more" template)
    $this->form['more'] = new sfForm;
    $ws = $this->form['more']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['more']->getValidatorSchema();
    $ws['deposit'] = new sfWidgetFormInputCheckbox(array(
    ));
    $vs['deposit'] = new sfValidatorBoolean(array(
      'required' => false,
    ));
    $this->form['more']->setDefault('deposit', $this->transaction->deposit);
    $ws['with_shipment'] = new sfWidgetFormInputCheckbox(array(
    ));
    $vs['with_shipment'] = new sfValidatorBoolean(array(
      'required' => false,
    ));
    $this->form['more']->setDefault('with_shipment', $this->transaction->with_shipment);

    // DESCRIPTION
    $this->form['description'] = new sfForm;
    $this->form['description']->setDefault('description', $this->transaction->description);
    $ws = $this->form['description']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['description']->getValidatorSchema();
    $ws['description'] = new sfWidgetFormTextarea();
    $vs['description'] = new sfValidatorString(array('required' => false,));

    // PRICES
    $this->form['price_new'] = new sfForm;
    $ws = $this->form['price_new']->getWidgetSchema()->setNameFormat('transaction[price_new][%s]');
    $vs = $this->form['price_new']->getValidatorSchema();
    $ws['qty'] = new sfWidgetFormInput(array('type' => 'number'), array('min' => -250, 'max' => 250));
    $vs['qty'] = new sfValidatorInteger(array(
      'max' => 251,
      'required' => false, // if no qty is set, then "1" is used
    ));
    $ws['price_id'] = new sfWidgetFormInputHidden;
    $vs['price_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Price',
      'required' => false,
      // already includes in PriceTable the control of user's credentials
    ));
    $ws['declination_id'] = new sfWidgetFormInputHidden;
    //$vs['declination_id'] <-- this is done depending on the submitted type, in the "complete" action
    $ws['type'] = new sfWidgetFormInputHidden;
    $vs['type'] = new sfValidatorChoice(array(
      'choices' => array('declination', 'gauge'),
    ));
    $ws['bunch'] = new sfWidgetFormInputHidden;
    $vs['bunch'] = new sfValidatorChoice(array(
      'choices' => array('museum', 'manifestations', 'store'),
    ));
    $ws['id'] = new sfWidgetFormInputHidden;
    $vs['id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Transaction',
      'query' => Doctrine::getTable('Transaction')->createQuery('t')
        ->andWhere('t.closed = ?', false)
        ->andWhere('t.id = ?', $this->transaction->id),
    ));
    $this->form['price_new']->setDefault('id', $this->transaction->id);
    $ws['state'] = new sfWidgetFormInputHidden;
    $vs['state'] = new sfValidatorChoice(array(
      'choices' => array('', 'integrated'),
      'required' => false,
    ));
    $ws['free-price'] = new sfWidgetFormInputHidden;
    $ws['free-price']->setDefault((float)sfConfig::get('project_tickets_free_price_default', 1));
    $vs['free-price'] = new sfValidatorNumber(array(
      'min' => 0,
      'required' => false,
    ));
    
    // AUTO-SEAT
    $this->form['price_new']->seat = new sfForm;
    $ws = $this->form['price_new']->seat->getWidgetSchema()->setNameFormat('transaction[seat][%s]');
    $vs = $this->form['price_new']->seat->getValidatorSchema();
    $ws['gauge_id'] = new sfWidgetFormInputHidden;
    $vs['gauge_id'] = new sfValidatorDoctrineChoice(array(
        'model' => 'Gauge',
    ));
    $ws['qty'] = new sfWidgetFormInputHidden;
    $vs['qty'] = new sfValidatorInteger(array(
        'min' => 0,
    ));
    $ws['id'] = new sfWidgetFormInputHidden;
    $ws['id']->setDefault($this->transaction->id);
    $vs['id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Transaction',
      'query' => Doctrine::getTable('Transaction')->createQuery('t')
        ->andWhere('t.closed = ?', false)
        ->andWhere('t.id = ?', $this->transaction->id),
    ));
    
    // NEW "PRODUCTS"
    $this->form['content'] = array();
    $this->form['content']['manifestations'] = new sfForm;
    $ws = $this->form['content']['manifestations']->getWidgetSchema();
    $vs = $this->form['content']['manifestations']->getValidatorSchema();
    $vs['manifestation_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'query' => Doctrine::getTable('Manifestation')->createQuery('m')->select('m.id')
        ->andWhere('e.museum = ?', false)
        ->andWhere('m.reservation_confirmed = ? AND m.blocking = ?',array(true,true))
        ->andWhere('pu.sf_guard_user_id = ?', $this->getUser()->getId()),
    ));
    $this->form['content']['museum'] = new sfForm;
    $ws = $this->form['content']['museum']->getWidgetSchema();
    $vs = $this->form['content']['museum']->getValidatorSchema();
    $vs['manifestation_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'query' => Doctrine::getTable('Manifestation')->createQuery('m')->select('m.id')
        ->andWhere('e.museum = ?', true)
        ->andWhere('m.reservation_confirmed = ? AND m.blocking = ?',array(true,true))
        ->andWhere('pu.sf_guard_user_id = ?', $this->getUser()->getId()),
    ));
    $this->form['content']['store'] = new sfForm;
    $ws = $this->form['content']['store']->getWidgetSchema();
    $vs = $this->form['content']['store']->getValidatorSchema();
    $vs['product_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Product',
      'query' => Doctrine::getTable('Product')->createQuery('pdt')->select('pdt.id')
        ->andWhereIn('pdt.meta_event_id IS NULL OR pdt.meta_event_id', array_keys($this->getUser()->getMetaEventsCredentials()))
        ->leftJoin('pdt.Prices p')
        ->leftJoin('p.Users pu')
        ->andWhere('pu.sf_guard_user_id = ?', $this->getUser()->getId()),
    ));
    $this->form['content']['store']->integrate = new sfForm;
    $ws = $this->form['content']['store']->integrate->getWidgetSchema()->setNameFormat('transaction[store_integrate][%s]');
    $vs = $this->form['content']['store']->integrate->getValidatorSchema();
    $ws['id'] = new sfWidgetFormInputHidden;
    $vs['id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Transaction',
      'query' => Doctrine::getTable('Transaction')->createQuery('t')
        ->andWhere('t.closed = ?', false)
        ->andWhere('t.id = ?', $this->transaction->id),
    ));
    $ws['force'] = new sfWidgetFormInputHidden;
    $vs['force'] = new sfValidatorPass(array(
      'required' => false,
    ));
    $this->form['content']['store']->integrate
      ->setDefault('id', $this->transaction->id)
      ->setDefault('force', null)
    ;

    // MemberCard
    $this->form['content']['store']->member_card = new sfForm;
    $ws = $this->form['content']['store']->member_card->getWidgetSchema()->setNameFormat('member_card[%s]');
    $vs = $this->form['content']['store']->member_card->getValidatorSchema();
    $msf = new MemberCardForm();
    $this->form['content']['store']->member_card->_csrf_token = $msf->getCSRFToken();

    $ws['contact_id'] = new sfWidgetFormInputHidden;
    $vs['contact_id'] = new sfValidatorPass(array('required' => false,));
    
    $ws['member_card_type_id'] = new sfWidgetFormInputHidden;
    $vs['member_card_type_id'] = new sfValidatorPass(array('required' => false,));
    
    $ws['contact_id']->setDefault($this->transaction->contact_id);

    // GIFT COUPON
    $this->form['gift_coupon'] = new sfForm;
    $ws = $this->form['gift_coupon']->getWidgetSchema()->setNameFormat('transaction[gift_coupon][%s]');
    $vs = $this->form['gift_coupon']->getValidatorSchema();
    $ws['code'] = new sfWidgetFormInputText(array(
      'label' => 'Gift coupon',
    ), array(
      'placeholder' => __('Coupon code'),
    ));
    $vs['code'] = new sfValidatorString(array('required' => false));
    
    // NEW PAYMENT
    $this->form['payment_new'] = new sfForm;
    $ws = $this->form['payment_new']->getWidgetSchema()->setNameFormat('transaction[payment_new][%s]');
    $vs = $this->form['payment_new']->getValidatorSchema();
    $ws['payment_method_id'] = new sfWidgetFormDoctrineChoice(array(
      'expanded' => true,
      'model' => 'PaymentMethod',
      'method' => 'toStringEPT',
      'order_by' => array('name', ''),
      'query' => $q = Doctrine::getTable('PaymentMethod')->createQuery('pm')
        ->leftJoin('pm.PaymentMethodUser pmu WITH pmu.sf_guard_user_id = ?', $this->getUser()->getId())
        ->andWhere('pm.display = ?',true)
        ->andWhere('pmu.sf_guard_user_id IS NULL'),
    ));
    $vs['payment_method_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'PaymentMethod',
      'query' => $q,
    ));
    $vs['member_card_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'MemberCard',
      'query' => Doctrine::getTable('MemberCard')->createQuery('mc')
        ->andWhere('mc.expire_at > NOW()'),
      'required' => false,
    ));
    $ws['value'] = new sfWidgetFormInput(array(), array('pattern' => '-{0,1}\d+[,\.]{0,1}\d{0,2}'));
    $vs['value'] = new sfValidatorNumber(array('required' => false));
    $ws['detail'] = new sfWidgetFormInput;
    $vs['detail'] = new sfValidatorString(array('required' => false));
    $ws['created_at'] = new liWidgetFormJQueryDateText(array('culture' => $this->getUser()->getCulture(),));
    $vs['created_at'] = new sfValidatorDate(array(
      'required' => false,
      'min'     => strtotime('6 years ago 0:00'),
    ));

    // DELETE PAYMENT
    $this->form['payments_list'] = new sfForm;
    $ws = $this->form['payments_list']->getWidgetSchema()->setNameFormat('transaction[payments_list][%s]');
    $vs = $this->form['payments_list']->getValidatorSchema();
    $ws['id'] = new sfWidgetFormInputHidden;
    $vs['id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Payment',
      'query' => Doctrine::getTable('Payment')->createQuery('p')
        ->leftJoin('p.Transaction t')
        ->andWhere('t.closed = ?', false),
    ));

    // CLOSE THE TRANSACTION
    $this->form['close'] = new sfForm;
    $ws = $this->form['close']->getWidgetSchema()->setNameFormat('transaction[close][%s]');
    $vs = $this->form['close']->getValidatorSchema();
    $ws['id'] = new sfWidgetFormInputHidden;
    $vs['id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Transaction',
      'query' => Doctrine::getTable('Transaction')->createQuery('t')
        ->andWhere('t.closed = ?', false)
        ->andWhere('t.id = ?', $this->transaction->id),
    ));
    $this->form['close']->setDefault('id', $this->transaction->id);

    // Force a postal code input
    $q = Doctrine::getTable('OptionTck')->createQuery('o')
      ->andWhere('o.sf_guard_user_id IS NULL')
      ->andWhere('o.name = ?', 'tck-print-ticket-cp');

    $option = $q->fetchOne();
    $auth = false;
    if ($option) {
        $auth = $option->value;
    }
    sfConfig::set('app_transaction_OptionTck', $auth);

    // SIMPLIFIED GUI
    //$this->form['simplified']['manifestations'] = $this->form['content']['manifestations'];
  }

  public function executeRespawn(sfWebRequest $request)
  {
    $this->sf_request = $request;
  }
  public function executeAccess(sfWebRequest $request)
  {
    $transaction = Doctrine::getTable('Transaction')->findOneById($request->getParameter('id'));

    // reopen the transaction
    if ( $transaction->closed
      && $request->hasParameter('reopen')
      && $this->getUser()->hasCredential('tck-unblock')
    )
    {
      $transaction->closed = false;
      $transaction->save();
    }

    $this->redirect('transaction/edit?id='.$request->getParameter('id'));
  }

  public function executeComplete(sfWebRequest $request)
  {
    // initialization
    $this->executeEdit($request);
    $this->dealWithDebugMode($request);
    $vs['declination_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Gauge',
      'query' => Doctrine_Query::create()->from('Gauge g')
        ->leftJoin('g.Workspace w')
        ->leftJoin('w.Users wu')
        ->andWhere('wu.id = ?', $this->getUser()->getId()),
    ));

    require(dirname(__FILE__).'/complete.php');
    return '';
  }

  public function executeGetManifestations(sfWebRequest $request)
  { return $this->getAbstract($request, 'manifestations'); }
  public function executeGetPeriods(sfWebRequest $request)
  { return $this->getAbstract($request, 'museum'); }
  public function executeGetStore(sfWebRequest $request)
  { return $this->getAbstract($request, 'store'); }
  protected function getAbstract(sfWebRequest $request, $type)
  {
    // initialization
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink', 'Number'));
    $this->dealWithDebugMode($request);

    require(dirname(__FILE__).'/get-abstract.php');
    return '';
  }

  public function executeGetPayments(sfWebRequest $request)
  {
    // initialization
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink', 'Number'));
    $this->dealWithDebugMode($request);

    require(dirname(__FILE__).'/get-payments.php');
    return '';
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('I18N'));
    parent::executeNew($request);

    if ( $request->getParameter('professional_id').'' === ''.intval($request->getParameter('professional_id')) )
    {
      $pro = Doctrine::getTable('Professional')->find(intval($request->getParameter('professional_id')));
      if ( $pro )
      {
        $this->transaction->professional_id = $pro->id;
        $request->setParameter('contact_id', $pro->contact_id);
      }
    }
    if ( $request->getParameter('contact_id').'' === ''.intval($request->getParameter('contact_id')) )
      $this->transaction->contact_id = intval($request->getParameter('contact_id'));

    $this->dispatcher->notify(new sfEvent($this, 'tck.before_transaction_creation', array('transaction' => $this->transaction)));
    $this->transaction->save();
    $this->dispatcher->notify(new sfEvent($this, 'tck.after_transaction_creation', array('transaction' => $this->transaction)));

    $this->getUser()->setFlash('success', __('Transaction created'));
    $this->redirect('transaction/edit?id='.$this->transaction->id);
  }

  public function executeNewJson(sfWebRequest $request)
  {  
    $this->transaction = new Transaction();
    $this->transaction->save();
  }
  
  public function executeGetCSRFToken(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
    $form = new sfForm();
    $this->csrfToken = $form->getCSRFToken();
  }

  public function executeShow(sfWebRequest $request)
  { $this->redirect('transaction/edit?id='.$request->getParameter('id')); }
  public function executeBatchDelete(sfWebRequest $request)
  { $this->forward404('You are not supposed to be here...'); }
  public function executeDelete(sfWebRequest $request)
  { $this->forward404('You are not supposed to be here...'); }
  public function executeCreate(sfWebRequest $request)
  { $this->forward404('You are not supposed to be here...'); }
  public function executeUpdate(sfWebRequest $request)
  { $this->forward404('You are not supposed to be here...'); }

  public function executeSeatsFirst(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('Url'));
    $this->getUser()->setFlash('referer', url_for('transaction/closeWindow'));
    $this->redirect('ticket/seatsAllocation?type=close&id='.$request->getParameter('id').'&gauge_id='.$request->getParameter('gauge_id').'&add_tickets=true');
  }
  public function executeCloseWindow(sfWebRequest $request)
  { }

  protected function dealWithDebugMode(sfWebRequest $request)
  {
    $this->setTemplate('json');

    if ( $request->hasParameter('debug') && sfConfig::get('sf_web_debug', false) )
    {
      $this->getResponse()->setContentType('text/html');
      $this->setLayout('layout');
    }
    else
    {
      sfConfig::set('sf_debug',false);
      sfConfig::set('sf_escaping_strategy', false);
    }
  }

  public function executeDirectSurveys(sfWebRequest $request)
  {
    $this->transaction = Doctrine::getTable('Transaction')->find($request->getParameter('id'));
	  $this->forward404Unless($this->transaction);

    $this->forms = array();
    foreach ( $surveys = $this->transaction->getSurveys() as $survey )
      $this->forms[] = new SurveyDirectForm($survey, array('transaction' => $this->transaction));
  }

  public function executeCommitDirectSurvey(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');

    $transaction = Doctrine::getTable('Transaction')->find($request->getParameter('transaction_id', 0));
	  $this->forward404Unless($transaction);

    $params = $request->getParameter('survey', array());

    $s = new Survey;
    foreach ( $surveys = $transaction->getSurveys() as $survey )
    if ( $survey->id == $params['id'] )
    {
      $s = $survey;
      break;
    }
    $this->form = new SurveyDirectForm($s, array('transaction' => $transaction));

    $this->form->bind($params);
    if ( !$this->form->isValid() )
    {
      return "Error";
    }
    else
    {
      $this->form->save();
    }
  }

  public function executeDirectPrintLog(sfWebRequest $request)
  {
    $transaction_id = $request->getParameter('id', 0);
    $transaction = Doctrine::getTable('Transaction')->find($transaction_id);
	  $this->forward404Unless($transaction);

    $directPrint = $request->getParameter('directPrint', array());
    $directPrint['transaction_id'] = $transaction_id;
    $form = new DirectPrintForm();
    $form->bind($directPrint);
    if ( !$form->isValid() ) {
      $errors = array();
      foreach ($form->getErrorSchema() as $key => $err) {
        if ($key)
          $errors[$key] = $err->getMessage();
      }
      throw new Exception ('Invalid DirectPrintForm: ' . print_r($errors, 1));
    }
    else
      $form->save();

    $this->getResponse()->setHttpHeader('Content-type','application/json');
    echo json_encode('Direct Print logged');
    die();

  }
}
