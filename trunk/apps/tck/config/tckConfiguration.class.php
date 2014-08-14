<?php

require_once dirname(__FILE__).'../../../../config/autoload.inc.php';

class tckConfiguration extends sfApplicationConfiguration implements liGarbageCollectorInterface
{
  protected $collectors = array();
  protected $task;
  
  public function configure()
  {
    parent::configure();
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
    
    $this->dispatcher->connect('user.change_authentication', array($this, 'logAuthentication'));
    $this->dispatcher->connect('tck.tickets_print', array($this, 'sendEmailOnPrintingTickets'));
  }
  
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
    
    if ( $transaction->professional_id )
      $email->Professionals[] = $transaction->Professional;
    else
      $email->Contacts[] = $transaction->Contact;
    
    $email->field_subject = $client['name'].': '.__('seat allocations for your order #%%transaction_id%%',array('%%transaction_id%%' => $transaction->id));
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
    
    return $email->save();
    
  } catch ( Exception $e ) {
    // avoid any mistake
    error_log($e->getMessage());
    return;
  } }

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
    if ( !$this->task )
      echo "$section: $message";
    else
      $this->task->logSection(str_pad($section,20), $message, null, $style);
  }
  public function initGarbageCollectors(sfCommandApplicationTask $task = NULL)
  {
    $this->task = $task;
    
    // WIP tickets collector
    $this->addGarbageCollector('wip', function(){
      $section = 'WIP tickets';
      $this->stdout($section, 'Deleting tickets...', 'COMMAND');
      $nb = Doctrine_Query::create()->from('Ticket tck')
        ->andWhere('tck.price_id IS NULL')
        ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL AND tck.cancelling IS NULL')
        ->andWhere('tck.updated_at < ?', date('Y-m-d H:i:s', strtotime(sfConfig::get('app_tickets_wip_timeout', '2 hours').' ago')))
        ->delete()
        ->execute()
      ;
      $this->stdout($section, "[OK] $nb tickets deleted", 'INFO');
    });
    
    // Asked tickets collector
    $this->addGarbageCollector('asked', function(){
      $section = 'Asked tickets';
      $this->stdout($section, 'Deleting too old tickets...', 'COMMAND');
      $q = Doctrine_Query::create()->from('Ticket tck')
        ->andWhere('tck.price_id IS NOT NULL')
        ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL AND tck.cancelling IS NULL')
        ->andWhere('tck.updated_at < ?', $date = date('Y-m-d H:i:s', strtotime(sfConfig::get('app_tickets_asked_timeout','1 hour').' ago')))
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
    
    // Close useless transactions
    $this->addGarbageCollector('close', function(){
      $section = 'Opened transactions';
      $this->stdout($section, 'Closing too old transactions...', 'COMMAND');
      
      $q = Doctrine::getTable('Transaction')->createQuery('t')
        ->select('t.id, t.closed')
        ->leftJoin('t.Payments p')
        ->andWhere('t.updated_at < ?', date('Y-m-d H:i:s', strtotime('1 day ago')))
        ->andWhere('t.closed = ?', false)
        ->groupBy('t.id, t.closed')
        ->having('count(tck.id) = 0 AND count(p.id) = 0')
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
}
