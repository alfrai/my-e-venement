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

class liPassbookPluginConfiguration extends sfPluginConfiguration
{
  public function setup()
  {
    liClassLoader::create()->register('Passbook', __DIR__ . '/../lib/vendor/');
    $this->dispatcher->connect('email.before_sending_tickets', array($this, 'listenToEmailedOrders'));
    $this->dispatcher->connect('pub.tickets_list_formats', array($this, 'listenToTicketsListFormats'));
    $this->dispatcher->connect('pub.transaction_generate_other_format', array($this, 'listenToTransactionGenerateOtherFormat'));
  }
  
  public function listenToTransactionGenerateOtherFormat(sfEvent $event)
  {
    if ( $event['format'] != 'passbook' )
      return;
    if ( $event['target'] != 'tickets' )
      return;
    
    try {
      $wallet = liPassbookWallet::create($event['transaction'])->buildArchive();
      $event['content'] = (string)$wallet;
      $event['headers'] = array(
        'Content-Disposition' => 'attachment; filename="'.$wallet->getFilename().'"',
        'Content-Type'        => liPassbookWallet::MIME_TYPE,
      );
    }
    catch ( Exception $e ) { $this->log('An error occurred generating a Passbook, skipping this step... Error: function listenToTransactionGenerateOtherFormat()', $e); }
  }
  public function listenToTicketsListFormats(sfEvent $event)
  { try {
    // the link helper
    if ( !sfContext::hasInstance() )
      throw new sfException('Cannot generate the Passbook link (no Context defined)');
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
    
    // the transaction
    $params = $event->getParameters();
    $transaction = $params['transaction'];
    
    // print the link
    echo link_to(
      'Passbook',
      'transaction/tickets?id='.$transaction->id.'&format=passbook',
      array(
        'class' => 'passbook',
        'title' => __('Especially for mobile devices')
      )
    ).' ';
  }
  catch ( Exception $e ) { $this->log('An error occurred generating a Passbook, skipping this step... Error: function listenToTicketsListFormats()', $e); } }
  
  public function listenToEmailedOrders(sfEvent $event)
  { try {
    $email = $event['email'];
    $params = $event->getParameters();
    if ( $email->getType() !== 'Order' || !$params['transaction'] instanceof Transaction )
      return;
    
    foreach ( $params['transaction']->Tickets as $ticket )
    {
      $pass = new liPassbook($ticket);
      
      $attachment = new Attachment;
      $attachment->filename = $pass->getRealFilePath();
      $attachment->original_name = basename($pass->getPkpassPath());
      $attachment->mime_type = $pass->getMimeType();

      $attachment->email_id = $email->id;
      $attachment->save();
      
      // and then, to be sure that the attachments collection is up2date
      $email->Attachments[] = $attachment;
    }
  }
  catch ( Exception $e ) { $this->log('An error occurred generating a Passbook, skipping this step... Error: function listenToEmailedOrders()', $e); } }
  
  /**
   * Function that helps making dispatcher calls fail-proof
   * @param $e Exception
   * @return void
   **/
  public function log($msg = '', Exception $e)
  {
    error_log($msg.' / '.$e->getMessage());
    if ( sfContext::hasInstance() && sfConfig::get('sf_web_debug', false) )
      throw $e;
  }
  
  /**
   * returns the dispatcher
   * @return sfEventDispatcher
   **/
  public function getDispatcher()
  {
    return $this->dispatcher;
  }
}

