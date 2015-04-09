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
class ParseApacheLogsTask extends sfBaseTask{

  protected function configure() {
    $this->addArguments(array(
      new sfCommandArgument('input', sfCommandArgument::REQUIRED, 'The file to parse'),
      new sfCommandArgument('domain', sfCommandArgument::REQUIRED, 'Your domain name'),
    ));
    $this->addOptions(array(
      new sfCommandOption('output', null, sfCommandOption::PARAMETER_OPTIONAL, 'The file to write the CSV result'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'stats'),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'apache-logs-parser';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration, $options['env']);
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    if ( $options['output'] && substr($options['output'], 0, 1) != '/' )
      $options['output'] = sfConfig::get('sf_app_cache_dir','/tmp/').'/'.$options['output'];
    if ( substr($arguments['input'], 0, 1) != '/' )
      $arguments['input'] = __DIR__.'/../../'.$arguments['input'];
    
    $parser = new ApacheLogParser;
    if ( !$parser->open_log_file($arguments['input']) )
    {
      $this->logSection('File', 'Failed to open the input file '.$arguments['input'], 'ERROR');
      return;
    }
    
    $this->logSection('Domain', $arguments['domain']);
    $this->logSection('File', 'Success');
    $last = array();
    while ( $line = $parser->get_line() )
    {
      $log = $parser->format_line($line);
      if ( $log === false || strpos($log['referer'], $arguments['domain']) !== false )
        continue;
      $this->logSection('Line', $log['date'].' '.$log['time'].' - '.$log['ip'].' '.$log['referer']);
      
      $date = DateTime::createFromFormat('d/M/Y H:i:s', $log['date'].' '.$log['time']);
      $q = Doctrine::getTable('Transaction')->createQuery('t')
        ->leftJoin('t.Payments p')
        ->where("t.created_at + '2 seconds'::interval > ? AND t.created_at - '2 seconds'::interval < ?", array($date->format('Y-m-d H:i:s'), $date->format('Y-m-d H:i:s')))
        ->orWhere("p.created_at + '3 seconds'::interval > ? AND p.created_at - '3 seconds'::interval < ?", array($date->format('Y-m-d H:i:s'), $date->format('Y-m-d H:i:s')))
      ;
      $transaction = $q->fetchOne();
      if ( !$transaction )
      {
        $this->logSection('WebOrigin', 'No transaction found for this visit', 'ERROR');
        continue;
      }
      
      $wo = new WebOrigin;
      $wo->Transaction = $transaction;
      $wo->ipaddress = $log['ip'];
      $wo->referer = $log['referer'];
      $wo->first_page = $log['path'];
      $wo->user_agent = $log['agent'];
      $wo->save();
      $wo->created_at = $date->format('Y-m-d H:i:s');
      $wo->save();
      $this->logSection('WebOrigin', 'Created for transaction #'.$transaction->id);
    }
    
    $parser->close_log_file();
    $this->logSection('File', 'Closed');
    
    $q = Doctrine_Query::create()->from('Transaction t')
      ->andWhere('(SELECT count(wo.id) > 1 FROM web_origin wo WHERE wo.transaction_id = t.id)')
    ;
    $cpt = 0;
    foreach ( $q->execute() as $transaction )
    {
      $cpt += Doctrine_Query::create()->from('WebOrigin wo')
        ->andWhere('wo.transaction_id = ?', $transaction->id)
        ->andWhere('wo.id != (SELECT MIN(woo.id) FROM WebOrigin woo WHERE woo.transaction_id = ?)', $transaction->id)
        ->delete()
        ->execute();
    }
    $this->logSection('Clean', 'Useless WebOrigins ... '.$cpt.' ... done');
  }
}
