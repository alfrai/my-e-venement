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
class RebuildTicketsQrcodeTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('go', sfCommandArgument::REQUIRED, 'Go ? (anything)'),
      new sfCommandArgument('debug', sfCommandArgument::OPTIONAL, 'Debug mode', false),
    ));
    $this->addOptions(array(
      new sfCommandOption('force', null, sfCommandOption::PARAMETER_REQUIRED, 'Force the tickets\'s QRCode rebuilding (y/N)', 'n'),
      new sfCommandOption('send-email', null, sfCommandOption::PARAMETER_REQUIRED, 'Send an email to concerned people (y/N).', 'n'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'prod'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'tck'),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'rebuild-tickets-qrcode';
    $this->briefDescription = 'Rebuild Tickets\' QRCode';
    $this->detailedDescription = 'Be careful, if the project\'s salt has changed, tickets generated before this action will not validate anymore.';
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration, $options['env']);
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    $q = Doctrine_Query::create()->from('Ticket tck')
      ->orderBy('tck.id');
    if ( $options['force'] != 'y' )
      $q->andWhere('tck.barcode IS NULL OR tck.barcode = ?', '');
    
    $res = array('success' => 0, 'error' => 0);
    foreach ( $q->execute() as $ticket )
    {
      if ( !$ticket->price_id )
        continue;
      if ( $ticket->barcode && $options['force'] != 'y' )
        continue;
      
      $ticket->barcode = NULL;
      $r = $ticket->trySave();
      
      if ( $arguments['debug'] !== false )
        $this->logSection('Ticket', '#'.$ticket->id.' '.$ticket->barcode);
      $res[$r ? 'success' : 'error']++;
    }
    
    $cpt = 0;
    if ( $options['send-email'] == 'y' )
    {
      // TODO: send conditionnally
      $cpt++;
    }
    
    if ( $res['success'] > 0 )
      $this->logSection('Barcodes', $res['success'].' Ticket(s) have been updated.');
    if ( $res['error'] > 0 )
      $this->logSection('Barcodes', $res['success'].' Ticket(s) failed to be updated.', null, 'ERROR');
    if ( $cpt > 0 )
      $this->logSection('Emails', $cpt.' email(s) have been sent to customers.');
  }
}
