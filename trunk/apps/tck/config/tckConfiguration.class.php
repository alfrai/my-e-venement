<?php

require_once dirname(__FILE__).'../../../../config/autoload.inc.php';

class tckConfiguration extends sfApplicationConfiguration
{
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
    
    $auth = new Authentication();
    $auth->sf_guard_user_id = $user->getId();
    $auth->description      = $user;
    $auth->ip_address       = $request->getHttpHeader('addr','remote');
    $auth->user_agent       = $request->getHttpHeader('User-Agent');
    $auth->referer          = $request->getReferer();
    $auth->success          = $params['authenticated'];
    
    $auth->save();
  }
}
