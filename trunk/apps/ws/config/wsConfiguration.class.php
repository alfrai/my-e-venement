<?php

require_once dirname(__FILE__).'../../../../config/autoload.inc.php';

class wsConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    parent::configure();
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
  }
  
  public static function getData($arr)
  {
    if ( !is_array($arr) )
      $r = json_decode($arr,true);
    
    $cs = $r['checksum'];
    unset($r['checksum']);
    
    if ( md5(json_encode($r).sfContext::getInstance()->getUser()->getAttribute('salt')) != $cs )
      throw new sfSecurityException('Unsafe content.');
    return $r;
  }
  
  public static function addChecksum($arr)
  {
    $arr['checksum'] = md5(json_encode($arr).sfContext::getInstance()->getUser()->getAttribute('salt'));
    return $arr;
  }
  public static function formatData($arr)
  {
    return json_encode(self::addChecksum($arr));
  }
  
  public static function authenticate(sfWebRequest $request)
  {
    $auth = new RemoteAuthenticationForm();
    $auth->bind(array('key' => $request->getParameter('key'),'ipaddress' => $request->getRemoteAddress()),array(),true);
    
    if ( !$auth->isValid() )
      throw new sfSecurityException("Unable to login distant service.");
    
    return $auth;
  }

  public function initGarbageCollectors(sfCommandApplicationTask $task = NULL)
  {
    $this->task = $task;
    
    if ( !sfConfig::get('app_failover_enable', false) )
      return $this;
    
    // too-old online transactions collector
    $this->addGarbageCollector('failover', function(){
      $ping = Doctrine::getTable('SlavePing')->createQuery('p')
        ->orderBy('p.created_at DESC')
        ->fetchOne();
      $section = 'failover';
      
      if ( !$ping                   // no "ping" recorded
        || $ping->state == 'end'    // the failover mechanism has been stopped normally
        || $ping->state != 'error' && strtotime($ping->created_at) > strtotime(sfConfig::get('app_failover_warning', '1 minute 20 seconds').' ago') )
        $this->stdout($section, '[OK] The SLAVE status is UP', 'INFO');
      elseif ( $ping->state == 'error' ) // the mechanism is in a failure state...
        $this->stdout($section, '[KO] The SLAVE status is DOWN, but it is not new...', 'ERROR');
      elseif ( strtotime($ping->created_at) > strtotime(sfConfig::get('app_failover_warning', '2 minutes 30 seconds').' ago') )
      {
        $this->sendEmails('warning');
        $this->stdout($section, '[--] The SLAVE status is DOUBTFUL', 'NOTICE');
      }
      else
      {
        // TODO: email + trigger
        $this->sendEmails('timeout')
          ->pullTriggers()
          ->stop()
        ;
        $this->stdout($section, '[KO] The SLAVE status is DOWN', 'ERROR');
      }
    });
    
    return $this;
  }
  
  protected function pullTriggers()
  {
    $triggers = sfConfig::get('app_failover_triggers', array());
    if ( !isset($triggers['master']) )
      return $this;
    
    touch($triggers['master']);
    return $this;
  }
  protected function stop()
  {
    $ping = new SlavePing;
    $ping->state = 'error';
    $ping->created_at = date('Y-m-d H:i:s');
    $ping->save();
    
    return $this;
  }
  
  protected function sendEmails($level = 'timeout')
  {
    if ( $addresses = sfConfig::get('app_failover_emails', array()) )
    {
      if ( !is_array($addresses) )
        $addresses = array($addresses);
      $email = new Email;
      $email->to = $addresses;
      $email->field_from = $addresses[0];
      $email->field_subject = sprintf('[FAILOVER] %s', $level != 'timeout'
        ? 'the failover mechanism of e-venement is about to be triggered'
        : 'the failover mechanism of e-venement has been triggered!!');
      
      $firm = sfConfig::get('project_about_firm', array('name' => 'Libre Informatique'));
      
      if ( $level != 'timeout' )
        $email->content = sprintf(<<<EOF
It has been at least %s that your SLAVE host has not been
heard from. Please check VERY quickly every connection
(power, network, ...) to be sure that the failover
mechanism will not be triggerred for any obvious reason.
<br/><br/>The trigger will be pulled after %s of deafness,
so the countdown is already started...
EOF
        , sfConfig::get('app_failover_warning', '1 minute 20 seconds')
        , sfConfig::get('app_failover_error', '2 minutes 30 seconds'));
      else
        $email->content = sprintf(<<<EOF
It has been at least %s that your e-venement SLAVE host
has not been heard from. The failover mechanism has been
triggered and cannot be reversed without the special
intervention of a qualified technician (data loss risks).
For this matter, please contact %s. During this laptime
your online systems are strictly unavailable.
<br/><br/>Thanks for your understanding.
<br/><br/><br/><br/>%s - %s
EOF
      , sfConfig::get('app_failover_error', '2 minutes 30 seconds')
      , $firm['name']
      , gethostname(), basename(getcwd()));
      
      $email->isATest(false);
      $email->setNoSpool();
      
      $email->save();
    }
    
    return $this;
  }
}
