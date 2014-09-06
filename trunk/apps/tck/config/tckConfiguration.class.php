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
    $this->enablePlugins(array('liClassLoaderPlugin', 'sfDomPDFPlugin'));
    parent::setup();
  }
  public function configure()
  {
    parent::configure();
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
    
    $this->dispatcher->connect('user.change_authentication', array($this, 'logAuthentication'));
    $this->dispatcher->connect('tck.tickets_print', array($this, 'sendEmailOnPrintingTickets'));
    $this->dispatcher->connect('tck.products_integrate', array($this, 'sendEmailOnIntegratingProducts'));
    $this->dispatcher->connect('tck.before_transaction_creation', array($this, 'activateConfirmationEmails'));
  }
  public function initialize()
  {
    $this->enableSecondWavePlugins($arr = sfConfig::get('app_options_plugins', array()));
    ProjectConfiguration::initialize();
  }
  
  // force sending emails on every transactions, depends on app.yml parameters
  public function activateConfirmationEmails(sfEvent $event)
  { try {
    if ( sfConfig::get('app_transaction_always_send_confirmation_email', false) )
      $event['transaction']->send_an_email = true;
  } catch ( Exception $e ) { return $this->catchError($e); } }
  
  public function sendEmailOnPrintingTickets(sfEvent $event)
  { try {
    $params = $event->getParameters();
    $transaction = $params['transaction'];
    
    if ( !$transaction->send_an_email )
      return false;
    
    $content = $transaction->renderSimplifiedTickets(array('css' => true, 'tickets' => false));
    $cpt = 0;
    foreach ( $params['tickets'] as $ticket )
    if ( strtotime($ticket->Manifestation->happens_at) > time() )
    {
      $cpt++;
      $content .= $ticket->renderSimplified();
    }
    
    if ( $cpt == 0 )
      return false;
    
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
    $email->field_from = $params['user']->getGuardUser()->email_address ? $params['user']->getGuardUser()->email_address : 'noreply@'.str_replace(array_keys($sr), array_values($sr), $client['url']);
    
    $email->not_a_test = true;
    $email->setNoSpool();
    
    // attachments, tickets in PDF
    $pdf = new sfDomPDFPlugin();
    $pdf->setInput($transaction->renderSimplifiedTickets(array('barcode' => 'png')));
    $pdf = $pdf->render();
    file_put_contents(sfConfig::get('sf_upload_dir').'/'.($filename = 'tickets-'.$transaction->id.'-'.date('YmdHis').'.pdf'), $pdf);
    $attachment = new Attachment;
    $attachment->filename = $filename;
    $attachment->original_name = $filename;
    $email->Attachments[] = $attachment;
    $attachment->save();

    return $email->save();
    
  } catch ( Exception $e ) { return $this->catchError($e); } }

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
  
  protected function stdout($section, $message, $style = 'INFO')
  {
    $section = str_pad($section,20);
    if ( !$this->task )
      echo "$section $message";
    else
      $this->task->logSection($section, $message, null, $style);
  }
  public function initGarbageCollectors(sfCommandApplicationTask $task = NULL)
  {
    $this->task = $task;
    
    // WIP tickets collector
    $this->addGarbageCollector('wip', function(){
      $timeout = sfConfig::get('app_tickets_timeout', array());
      $section = 'WIP tickets';
      $this->stdout($section, 'Deleting tickets...', 'COMMAND');
      $nb = Doctrine_Query::create()->from('Ticket tck')
        ->andWhere('tck.price_id IS NULL')
        ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL AND tck.cancelling IS NULL')
        ->andWhere('tck.updated_at < ?', date('Y-m-d H:i:s', strtotime(($timeout['wip'] ? $timeout['wip'] : '2 hours').' ago')))
        ->delete()
        ->execute()
      ;
      $this->stdout($section, "[OK] $nb tickets deleted", 'INFO');
    });
    
    // Asked tickets collector
    $this->addGarbageCollector('asked', function(){
      $timeout = sfConfig::get('app_tickets_timeout', array());
      $section = 'Asked tickets';
      $this->stdout($section, 'Deleting too old tickets...', 'COMMAND');
      $q = Doctrine_Query::create()->from('Ticket tck')
        ->andWhere('tck.price_id IS NOT NULL')
        ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL AND tck.cancelling IS NULL AND tck.duplicating IS NULL')
        ->andWhere('tck.updated_at < ?', $date = date('Y-m-d H:i:s', strtotime(($timeout['asked'] ? $timeout['asked'] : '1 hour').' ago')))
        ->leftJoin('tck.Transaction t')
        ->leftJoin('t.Order o')
        ->select('tck.id')->groupBy('tck.id')
        ->having('count(o.id) = 0')
      ;
      $tickets = $q->execute();
      $nb = $tickets->count();
      $tickets->delete();
      $this->stdout($section, "[OK] $nb tickets deleted", 'INFO');
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
        ->select('tck.id')->groupBy('tck.id')
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
      
      $q = Doctrine::getTable('Transaction')->createQuery('t')
        ->select('t.id, t.closed')
        ->leftJoin('t.Payments p')
        ->andWhere('t.updated_at < ?', date('Y-m-d H:i:s', strtotime('1 day ago')))
        ->andWhere('t.closed = ?', false)
        ->leftJoin('t.BoughtProducts bp WITH bp.integrated_at IS NOT NULL')
        ->groupBy('t.id, t.closed')
        ->having('count(tck.id) = 0 AND count(p.id) = 0 AND count(bp.id) = 0')
      ;
      $transactions = $q->execute();
      foreach ( $transactions as $transaction )
      {
        $transaction->closed = true;
        $transaction->save();
      }
      
      $this->stdout($section, '[OK] '.$transactions->count().' transactions closed', 'INFO');
    });
    
    return $this;
  }
  public function executeGarbageCollectors($names = NULL)
  {
    if ( is_null($names) )
      $names = array_keys($this->collectors);
    
    if ( !is_array($names) )
      $names = array($names);
    
    foreach ( $names as $name )
    {
      $fct = $this->getGarbageCollector($name);
      if ( $fct instanceof Closure )
        $fct();
    }
    
    return $this;
  }
  public function getGarbageCollector($name)
  {
    if ( !isset($this->collectors[$name]) )
      return FALSE;
    return $this->collectors[$name];
  }
  public function addGarbageCollector($name, Closure $function)
  {
    if ( isset($this->collectors[$name]) )
      throw new liEvenementException('A collector with the name "'.$name.'" already exists. Maybe you wanted to replace it ?');
    return $this->addOrReplaceGarbageCollector($name, $function);
  }
  public function addOrReplaceGarbageCollector($name, Closure $function)
  {
    $this->collectors[$name] = $function;
    return $this;
  }
  protected function catchError(Exception $e)
  {
    // avoid any mistake
    error_log($e->getMessage());
    return;
  }
}
