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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require_once dirname(__FILE__).'../../../../config/autoload.inc.php';

class tckConfiguration extends sfApplicationConfiguration
{
  protected $collectors = array();
  protected $task;
  protected $init_configuration = false;
  
  public function setup()
  {
    if (!( sfContext::hasInstance() && get_class(sfContext::getInstance()->getConfiguration()) != get_class($this) ))
      $this->enablePlugins(array('liClassLoaderPlugin', 'sfDomPDFPlugin', 'liBarcodePlugin'));
    parent::setup();
  }
  public function configure()
  {
    parent::configure();
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
  }
  public function initialize()
  {
    $conf = sfConfig::get('app_transaction_email', array());
    
    $this->dispatcher->connect('user.change_authentication', array($this, 'logAuthentication'));
    $this->dispatcher->connect('tck.tickets_print', array($this, 'sendEmailOnPrintingTickets'));
    
    if ( isset($conf['products']) && !in_array($conf['products'], array('never', false)) )
      $this->dispatcher->connect('tck.products_integrate', array($this, 'sendEmailOnIntegratingProducts'));
    
    if ( isset($conf['always_send_confirmation']) && $conf['always_send_confirmation'] )
      $this->dispatcher->connect('tck.before_transaction_creation', array($this, 'activateConfirmationEmails'));
    
    if ( sfConfig::get('app_tickets_auto_integrate', false) )
      $this->dispatcher->connect('tck.before_trying_to_close_transaction', array($this, 'productsIntegration'));
    
    if (!( sfContext::hasInstance() && get_class(sfContext::getInstance()->getConfiguration()) != get_class($this) ))
      $this->enableSecondWavePlugins($arr = sfConfig::get('app_options_plugins', array()));
    ProjectConfiguration::initialize();
  }
  
  public function productsIntegration($event)
  {
    if (!( isset($event['transaction']) && $event['transaction'] instanceof Transaction ))
      return;
    
    $cpt = 0;
    $paid = $event['transaction']->getPaid();
    foreach ( $event['transaction']->getItemables() as $pdt )
    if ( !$pdt->integrated_at )
    {
      $pdt->integrated_at = date('Y-m-d H:i:s');
      if ( $event['transaction']->getPrice(false, true) > $paid
        || $pdt instanceof Ticket && $pdt->needsSeating() )
        $pdt->integrated_at = NULL;
      else
        $cpt++;
    }
    
    $event->setReturnValue($cpt);
  }
  
  // force sending emails on every transactions, depends on app.yml parameters
  public function activateConfirmationEmails($event)
  { try {
    $event['transaction']->send_an_email = true;
  } catch ( Exception $e ) { return $this->catchError($e); } }
  
  public function sendEmailOnPrintingTickets(sfEvent $event)
  { try {
    $cpt = 0;
    foreach ( $event['tickets'] as $ticket )
    if ( strtotime($ticket->Manifestation->happens_at) > time() )
      $cpt++;
    if ( $cpt == 0 )
      return false;
    
    $with = array('barcode' => 'png');
    if ( $event['tickets'] )
      $with['only'] = $event['tickets'];
    
    $email = $this->genericSendEmailOn(
      $event,
      $event['transaction']->renderSimplifiedTickets($with),
      'tickets'
    );
    $this->dispatcher->notify(new sfEvent($this, 'email.before_sending_transaction_part', $email->getDispatcherParameters() + array('email' => $email)));
    $this->dispatcher->notify(new sfEvent($this, 'email.before_sending_tickets', $email->getDispatcherParameters() + array('email' => $email)));
    $email->save();
  } catch ( Exception $e ) { return $this->catchError($e); } }

  public function sendEmailOnIntegratingProducts(sfEvent $event)
  { try {
    $go = true;
    $conf = sfConfig::get('app_transaction_email', array());
    if ( isset($conf['products']) && $conf['products'] === 'e-product' )
    {
      $go = false;
      foreach ( $event['products'] as $prod )
      if ( $prod->description_for_buyers )
      {
        $go = true;
        break;
      }
    }
    if ( !$go )
      return;
    
    $email = $this->genericSendEmailOn(
      $event,
      $event['transaction']->renderSimplifiedProducts(),
      'tickets'
    );
    $this->dispatcher->notify(new sfEvent($this, 'email.before_sending_transaction_part', $email->getDispatcherParameters() + array('email' => $email)));
    $this->dispatcher->notify(new sfEvent($this, 'email.before_sending_products', $email->getDispatcherParameters() + array('email' => $email)));
    $email->save();
  } catch ( Exception $e ) { return $this->catchError($e); } }
  
  protected function genericSendEmailOn(sfEvent $event, $content, $type = 'content')
  {
    $conf = sfConfig::get('app_transaction_email', array());
    $transaction = $event['transaction'];
    
    if ( !$transaction->send_an_email
      || !(isset($conf['always_send_confirmation']) && $conf['always_send_confirmation'])
      || !($transaction->professional_id && $transaction->Professional->contact_email) && !($transaction->contact_id && $transaction->Contact->email)
    )
      throw new liEvenementException('You have tried to send an email without the ability for (no contact_id, no professional_id, no "send_an_email" field set to "true")...');
    
    $client   = sfConfig::get('project_about_client');
    $firm     = sfConfig::get('project_about_firm');
    $software = sfConfig::get('project_about_software', array('name' => 'e-venement', 'url' => 'http://www.e-venement.net/',));
    
    $email = new Email;
    $email->setType('Order')->addDispatcherParameter('transaction', $transaction);
    
    if ( $transaction->professional_id )
      $email->Professionals[] = $transaction->Professional;
    else
      $email->Contacts[] = $transaction->Contact;
    
    $email->field_subject = $client['name'].': '.__('your order #%%transaction_id%% has been updated',array('%%transaction_id%%' => $transaction->id));
    $email->content = nl2br($content);
    $email->content .= nl2br(sprintf(<<<EOF
-- 
<a href="%s">%s</a>
%s

%s
%s
EOF
    , $client['url'], $client['name']
    , $client['address']
    , __('By %%firm%%', array('%%firm%%' => '<a href="'.$firm['url'].'">'.$firm['name'].'</a>'))
    , __('Empowered by %%software%%', array('%%software%%' => '<a href="'.$software['url'].'">'.$software['name'].'</a>'))
    ));
    
    $sr = array('http://' => '', 'www.' => '',);
    $email->field_from = $event['user']->getGuardUser()->email_address ? $event['user']->getGuardUser()->email_address : 'noreply@'.str_replace(array_keys($sr), array_values($sr), $client['url']);
    $email->field_bcc = $email->field_from;
    
    // Bcc:
    try
    {
      $conf = sfConfig::get('app_transaction_email', array());
      $valid = new liValidatorEmail;
      if ( isset($conf['send_bcc_to']) && $valid->clean($conf['send_bcc_to']) )
        $email->field_bcc = $conf['send_bcc_to'];
      else
        $email->field_bcc = $email->field_from;
    }
    catch ( sfValidatorError $e )
    {
      error_log('Cannot send a Bcc: of this transaction, bad configuration parameter: '.$conf['send_bcc_to']);
      $email->field_bcc = $email->field_from;
    }
    
    // attachments, tickets in PDF
    $pdf = new sfDomPDFPlugin();
    $pdf->setInput($event->getSubject()->getPartial('global/get_tickets_pdf', array('tickets_html' => $content)));
    $pdf = $pdf->render();
    file_put_contents(sfConfig::get('sf_upload_dir').'/'.($filename = $type.'-'.$transaction->id.'-'.date('YmdHis').'.pdf'), $pdf);
    $attachment = new Attachment;
    $attachment->filename = $filename;
    $attachment->original_name = $filename;
    $email->Attachments[] = $attachment;
    $attachment->save();
    
    $email->isATest(false);
    $email->setNoSpool();
    
    return $email;
  }
  
  public function logAuthentication(sfEvent $event)
  {
    $params   = $event->getParameters();
    $user     = sfContext::getInstance()->getUser();
    $request  = sfContext::getInstance()->getRequest();
    
    if ( !is_object($user) )
      return false;
    
    if (( sfConfig::get('project_login_alert_beginning_at', false) && sfConfig::get('project_login_alert_beginning_at') < time() || !sfConfig::get('project_login_alert_beginning_at', false) )
      &&( sfConfig::get('project_login_alert_ending_at', false) && sfConfig::get('project_login_alert_ending_at') > time() || !sfConfig::get('project_login_alert_ending_at', false) )
      && sfConfig::get('project_login_alert_message', false) )
      $user->setFlash('error', sfConfig::get('project_login_alert_message'));

    $auth = new Authentication();
    $auth->sf_guard_user_id = $user->getId();
    $auth->description      = $user;
    $auth->ip_address       = $request->getHttpHeader('addr','remote');
    $auth->user_agent       = $request->getHttpHeader('User-Agent');
    $auth->referer          = $request->getReferer();
    $auth->success          = $params['authenticated'];
    
    $auth->save();
  }
  
  public function initGarbageCollectors(sfCommandApplicationTask $task = NULL)
  {
    $this->task = $task;
    
    // WIP tickets collector
    $this->addGarbageCollector('wip', function(){
      $timeout = sfConfig::get('app_tickets_timeout', array());
      $section = 'WIP tickets';
      $this->stdout($section, 'Resetting seats...', 'COMMAND');
      $tickets = Doctrine_Query::create()->from('Ticket tck')
        ->andWhere('tck.price_id IS NULL')
        ->andWhere('tck.seat_id IS NOT NULL')
        ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL AND tck.cancelling IS NULL')
        ->andWhere('tck.updated_at < ?', date('Y-m-d H:i:s', strtotime(($timeout['wip'] ? $timeout['wip'] : '2 hours').' ago')))
        ->execute()
      ;
      $nb = $tickets->count();
      foreach ( $tickets as $ticket )
        $ticket->seat_id = NULL;
      $tickets->save();
      $this->stdout($section, "[OK] $nb tickets resetted", 'INFO');
    });
    
    
    // try to auto-integrate tickets if setted up
    if ( sfConfig::get('app_tickets_auto_integrate', false) )
    $this->addGarbageCollector('auto-integrate', function(){
      $section = 'Asked itemables';
      $this->stdout($section, 'Integrating what we can...', 'COMMAND');
      $timeout = sfConfig::get('app_tickets_timeout', array());
      
      $q = Doctrine::getTable('Transaction')->createQuery('t', NULL, true)
        ->select('t.id, t.closed')
        ->leftJoin('t.Payments p')
        ->andWhere('t.updated_at < ?', $date = date('Y-m-d H:i:s', strtotime(($timeout['asked'] ? $timeout['asked'] : '1 hour').' - 1 minutes ago')))
        ->andWhere('t.closed = ?', false)
        ->orderBy('t.id')
      ;
      $transactions = $q->execute();
      $cpt = 0;
      foreach ( $transactions as $transaction )
      {
        $this->dispatcher->notify($event = new sfEvent($this, 'tck.before_trying_to_close_transaction', array(
          'transaction' => $transaction,
          'user'        => NULL,
        )));
        $transaction->save();
        if ( $event->getReturnValue() > 0 )
        {
          $cpt += $event->getReturnValue();
          $this->stdout($section, $event->getReturnValue().' itemables integrated for transaction #'.$transaction->id, 'DEBUG');
        }
      }
      $this->stdout($section, "[OK] globally, $cpt itemables were integrated", 'INFO');
    });
    
    // Asked tickets collector
    $this->addGarbageCollector('asked', function(){
      $timeout = sfConfig::get('app_tickets_timeout', array());
      $section = 'Asked tickets';
      $this->stdout($section, 'Resetting seats on squatters...', 'COMMAND');
      $q = Doctrine_Query::create()->from('Ticket tck')
        ->andWhere('tck.price_id IS NOT NULL')
        ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL AND tck.cancelling IS NULL AND tck.duplicating IS NULL')
        ->andWhere('tck.updated_at < ?', $date = date('Y-m-d H:i:s', strtotime(($timeout['asked'] ? $timeout['asked'] : '1 hour').' ago')))
        ->andWhere('tck.seat_id IS NOT NULL')
        ->leftJoin('tck.Transaction t')
        ->leftJoin('t.Order o')
        ->select('tck.id')->groupBy('tck.id')
        ->having('count(o.id) = 0')
      ;
      $tickets = $q->execute();
      $nb = $tickets->count();
      foreach ( $tickets as $ticket )
        $ticket->seat_id = NULL;
      $tickets->save();
      $this->stdout($section, "[OK] $nb tickets resetted", 'INFO');
    });
    
    // Asked products collector
    $this->addGarbageCollector('products', function(){
      $timeout = sfConfig::get('app_tickets_timeout', array());
      $section = 'Asked products';
      $this->stdout($section, 'Deleting too old products...', 'COMMAND');
      $q = Doctrine_Query::create()->from('BoughtProduct bp')
        ->andWhere('bp.integrated_at IS NULL')
        ->andWhere('bp.updated_at < ?', $date = date('Y-m-d H:i:s', strtotime(($timeout['asked'] ? $timeout['asked'] : '1 hour').' ago')))
        ->leftJoin('bp.Transaction t')
        ->leftJoin('t.Order o')
        ->select('bp.id')->groupBy('bp.id')
        ->having('count(o.id) = 0')
      ;
      $tickets = $q->execute();
      $nb = $tickets->count();
      $tickets->delete();
      $this->stdout($section, "[OK] $nb products deleted", 'INFO');
    });
    
    // Close useless transactions
    $this->addGarbageCollector('close', function(){
      $timeout = sfConfig::get('app_tickets_timeout', array());
      $section = 'Opened transactions';
      $this->stdout($section, 'Closing too old transactions...', 'COMMAND');
      
      $q = Doctrine::getTable('Transaction')->createQuery('t', 'printed', true)
        ->select('t.id, t.closed')
        ->leftJoin('t.Payments p')
        ->andWhere('t.updated_at < ?', date('Y-m-d H:i:s', strtotime('1 day ago')))
        ->andWhere('t.closed = ?', false)
        ->leftJoin('t.Order o')
        ->groupBy('t.id, t.closed')
        ->having('count(tck.id) = 0 AND count(p.id) = 0 AND count(bp.id) = 0 AND count(o.id) = 0')
      ;
      $transactions = $q->execute();
      foreach ( $transactions as $transaction )
      {
        $this->dispatcher->notify($event = new sfEvent($this, 'tck.before_trying_to_close_transaction', array(
          'transaction' => $transaction,
          'user'        => NULL,
        )));
        
        $transaction->closed = true;
        $transaction->save();
      }
      
      $this->stdout($section, '[OK] '.$transactions->count().' transactions closed', 'INFO');
    });
    
    return $this;
  }
}
