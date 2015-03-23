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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class SendNextManifestationsTask extends sfBaseTask{

  protected function configure() {
    $this->addArguments(array(
    ));
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'event'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'prod'),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'send-next-manifestations';
    $this->briefDescription = 'Sends the coming manifestations';
    $this->detailedDescription = <<<EOF
      The [snm:send-next-manifestations|INFO] sends the coming manifestations:
      [./symfony e-venement:send-next-manifestations --env=prod|INFO]
EOF;
  }

  protected function  execute($arguments = array(), $options = array()) {
    $period = sfConfig::get('app_synchronization_period',array('from' => 'now', 'to' => '+1 week'));
    $config = sfConfig::get('app_synchronization_config',array());
    
    if ( !$config )
    {
      $this->logSection('Setup','Not operational',null,'ERROR');
      return;
    }
    
    if ( !is_array($config['to']) )
      $config['to'] = array($config['to']);

    $databaseManager = new sfDatabaseManager($this->configuration);
    $context = sfContext::createInstance($this->configuration);
    
    $this->logSection('Period', sprintf('From %s to %s', $period['from'], $period['to']));
    
    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->andWhere('happens_at >= ?',date('Y-m-d H:i:s',strtotime($period['from']).' 0:00'))
      ->andWhere('happens_at <= ?',date('Y-m-d H:i:s',strtotime($period['to']).' 0:00'))
      ->orderBy('happens_at');
    $manifestations = $q->execute();
    
    if ( $manifestations->count() == 0 )
    {
      $this->logSection('Synchronization','Nothing to synchronize', null, 'COMMAND');
      return;
    }
    
    $this->logSection('Manifestations', sprintf('%d to be sent', $manifestations->count()));
    
    switch ( $config['format'] ) {
    case 'ical':
      $this->logSection('Synchronized by', 'iCal');
    default:
      $this->logSection('Synchronized by', 'Email');
      $email = new Email;
      $email->setMailer($this->getMailer());
      $email->to = $config['to'];
      $email->field_from = $config['from'];
      $email->field_subject = str_replace(array('%%from%%','%%to%%'),array($period['from'],$period['to']),$config['subject']);
      $email->content = $this->formatContent($manifestations);
      $email->not_a_test = true;
      $email->deleted_at = date('Y-m-d H:i:s');
      $email->save();
      $this->logSection('Synchronization', 'done');
      break;
    }
  }
  
  private function formatContent(Doctrine_Collection $manifestations)
  {
    $client = sfConfig::get('project_about_client');
    $period = sfConfig::get('app_synchronization_period');
    $this->configuration->loadHelpers(array('Tag','Url'));
    
    $str = sprintf('<h2>Manifestations report for %s from %s to %s :</h2>',$client['name'],$period['from'],$period['to']);
    
    $str .= sprintf('%d manifestations on the period',$manifestations->count());
    $str .= '<ul>';
    foreach ( $manifestations as $manifestation )
    {
      $nb = 0;
      foreach ( $manifestation->Gauges as $gauge )
        $nb += $gauge->value;
      $str .= '<li>'.$manifestation.' (for gauges at '.$nb.')</li>'."\n";
    }
    $str .= '</ul>';
    
    return $str;
  }
}
