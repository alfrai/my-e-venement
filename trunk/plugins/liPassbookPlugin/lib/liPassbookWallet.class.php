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

class liPassbookWallet extends ZipArchive
{
  protected $passes = array();
  protected $transaction, $file, $comment;
  const MIME_TYPE = 'application/zip';
  
  public static function create(Transaction $transaction, $filename = NULL)
  {
    return new self($transaction);
  }
  public function __construct(Transaction $transaction, $filename = NULL)
  {
    $this->transaction = $transaction;
    $this
      ->createPasses()
      ->setFilename($filename);
  }
  
  public function buildArchive()
  {
    if ( $this->open($this->getFullPath(), ZipArchive::CREATE) !== TRUE )
      throw new liEvenementException('A problem occurred when trying to create a Zip file for Passbooks (opening Zip file)');
    
    foreach ( $this->passes as $pass )
    {
      $this->addFile($pass->getRealFilePath(), $local = basename($pass->getPkpassPath()));
      $this->setCommentName($pass->getRealFilePath(), $local = 'Ticket #'.$pass->getTicket()->id.' for transaction #'.$this->transaction->id);
    }
    
    $this->close();
    $this->normalizePermissions();
    return $this;
  }
  public function __toString()
  {
    return file_get_contents($this->getFullPath());
  }
  
  public function setFilename($filename = NULL)
  {
    $this->file = $filename ? $filename : 'ticket-transaction-'.$this->transaction->id.'.pkpass.zip';
    
    if ( !is_dir(dirname($this->getFullPath())) )
      throw new liEvenementException('A problem occurred when trying to create a Zip file for Passbooks (directory does not exist)');
    
    error_log($this->getFullPath());
    return $this;
  }
  
  public function getMimeType()
  {
    return self::MIME_TYPE;
  }
  public function getTransaction()
  {
    return $this->transaction;
  }
  public function getFullPath()
  {
    return sfConfig::get('sf_app_cache_dir').'/'.$this->file;
  }
  public function getFilename()
  {
    return $this->file;
  }
  public function getPasses()
  {
    return $this->passes;
  }
  
  protected function normalizePermissions()
  {
    liPassbook::writeFile($this->getFullPath());
    return $this;
  }
  protected function createPasses()
  {
    foreach ( $this->transaction->Tickets as $ticket )
      $this->passes[] = new liPassbook($ticket);
    return $this;
  }
}
