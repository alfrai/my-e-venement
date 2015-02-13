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

/**
 * Ticket
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Ticket extends PluginTicket
{
  public function hasBeenCancelled($direction = 'both')
  {
    if ( $this->Cancelling->count() > 0 )
      return $this->Cancelling;
    
    if ( in_array($direction,array('both','down')) )
    foreach ( $this->Duplicatas as $dup )
    if ( $buf = $dup->hasBeenCancelled('down') )
      return $buf;
    
    if ( in_array($direction,array('both','up')) )
    if ( !is_null($this->duplicating) )
    if ( $buf = $this->Duplicated->hasBeenCancelled('up') )
      return $buf;
    
    return false;
  }
  
  public function getOriginal()
  {
    if ( is_null($this->duplicating) )
      return $this;
    
    return $this->Duplicated->getOriginal();
  }
  
  public function getQrcode($salt = NULL)
  {
    if ( !$this->id )
      return false;
    
    $salt = $salt
      ? $salt
      : sfConfig::get('project_eticketting_salt', '');
    
    if ( !$this->barcode )
      $this->barcode = md5('#'.$this->id.'-'.$salt);
    
    return $this->barcode;
  }
  
  public function renderBarcode($file = NULL) // PNG output directly to stdout
  {
    $bc = new liBarcode($this->qrcode);
    $bc->render($file);
    return $this;
  }
  
  public function getBarcodePng()
  {
    $bc = new liBarcode($this->qrcode);
    return (string)$bc;
  }
  
  public function needsSeating()
  {
    // if already seated
    if ( !is_null($this->seat_id) )
      return false;
    
    // if not seated, does it need seating ?
    $q = Doctrine::getTable('Location')->createQuery('l')
      ->leftJoin('l.Manifestations m')
      ->andWhere('m.id = ?', $this->manifestation_id)
      
      ->leftJoin('l.SeatedPlans sp')
      ->leftJoin('sp.Workspaces ws')
      ->leftJoin('ws.Gauges g')
      ->andWhere('g.id = ?', $this->gauge_id)
      
      ->select('l.id')
    ;
    return $q->count() > 0;
  }
  public function getIdBarcoded()
  {
    $c = ''.$this->id;
    $n = strlen($c);
    for ( $i = 12-$n ; $i > 0 ; $i-- )
      $c = '0'.$c;
    return $c;
  }
  
  public function getTotal()
  {
    return $this->value + $this->taxes;
  }
  
  public function renderSimplified($type = 'html')
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('Url', 'Number'));
    
    // the barcode
    if ( sfConfig::get('app_tickets_id', 'id') == 'id' )
    {
      $c = curl_init();
      curl_setopt_array($c, array(
        CURLOPT_URL => $url = public_path('/liBarcodePlugin/php-barcode/barcode.php?scale=3'.($type == 'html' ? '&mode=html' : '').'&code='.$this->getIdBarcoded(),true),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_RETURNTRANSFER => true,
      ));
      if (!( $barcode = curl_exec($c) ))
        error_log('Error loading the barcode: '.curl_error($c));
      curl_close($c);
    }
    else
      $barcode = $this->getBarcodePng();
    
    if ( $this->isModified() )
      $this->save();
    
    if ( $type != 'html' || sfConfig::get('app_tickets_id', 'id') != 'id' )
      $barcode = '<span><img src="data:image/jpg;base64,'.base64_encode($barcode).'" alt="#'.$this->id.'" /></span>';
    
    // the HTML code
    return sprintf(<<<EOF
  <div class="cmd-element ticket">
  <table><tr>
    <td class="desc">
      <div class="event"><table><tbody><tr><td><span>%s:</span> <span>%s</span></td></tr></tbody></table></div>
      <p class="event-2nd"><span>%s</span> <span>%s</span></p>
      <p class="description"><span>%s</span> <span>%s</span></p>
      <p class="location"><span>%s:</span> <span>%s</span></p>
      <p class="address"><span>%s:</span> <span>%s</span></p>
      <p class="gauge"><span>%s:</span> <span>%s</span></p>
      <p class="date"><span>%s:</span> <span>%s</span></p>
      <p class="price"><span>%s:</span> <span>%s</span> <span>%s</span></p>
      <p class="seat"><span>%s</span><span>%s</span></p>
      <div class="comment"><table><tbody><tr><td><div>%s</div></td></tr></tbody></table></div>
      <p class="ids"><span class="transaction">#%s</span> <span class="id">#%s</span></p>
      <p class="contact">%s</p>
      <p class="duplicate">%s</p>
    </td>
    <td class="bc">%s</td>
  <tr></table>
  <img class="background" src="data:image/png;base64,%s" alt="" />
  </div>
EOF
      , __('Event', null, 'li_tickets_email'), nl2br($this->Manifestation->Event)
      , '', $this->Manifestation->Event->subtitle
      , '', $this->Manifestation->Event->description
      , __('Venue', null, 'li_tickets_email'), (string)$this->Manifestation->Location
      , __('Address', null, 'li_tickets_email'), (string)$this->Manifestation->Location->full_address
      //, __('Category', null, 'li_tickets_email'), $this->Gauge->Workspace->on_ticket ? $this->Gauge->Workspace->on_ticket : (string)$this->Gauge
      , __('Category', null, 'li_tickets_email'), $this->category
      , __('Date', null, 'li_tickets_email'), $this->Manifestation->getFormattedDate()
      , __('Price', null, 'li_tickets_email'), $this->price_name, format_currency($this->value,'€')
      , $this->seat_id ? __('Seat #', null, 'li_tickets_email') : ($this->Manifestation->voucherized ? __('Voucher', null, 'li_ticket_email') : ''), $this->seat_id ? $this->Seat : ($this->Manifestation->Location->getWorkspaceSeatedPlan($this->Gauge->workspace_id) ? __('Not yet allocated', null, 'li_tickets_email') : '')
      , $this->comment ? $this->comment : sfConfig::get('project_eticketting_default_comment', __('This is your ticket', null, 'li_tickets_email'))
      , $this->transaction_id, $this->id
      , $this->contact_id
        ? $this->DirectContact->name_with_title
        : ($this->Transaction->contact_id ? __('Guest of %%contact%%', array('%%contact%%' => $this->Transaction->professional_id ? $this->Transaction->Professional->getFullName() : $this->Transaction->Contact->name_with_title), 'li_tickets_email') : '')
      , !$this->duplicating ? '' : __('This ticket is a duplicate of #%%tid%%, it replaces and cancels any previous version of this ticket you might have recieved', array('%%tid%%' => $this->transaction_id.'-'.$this->duplicating), 'li_tickets_email')
      , $barcode
      , base64_encode(file_get_contents(
        file_exists($file = sfConfig::get('sf_web_dir').'/private/ticket-simplified-layout.png')
          ? $file
          : sfConfig::get('sf_web_dir').'/images/ticket-simplified-layout-100dpi.png'
      ))
    );
  }
  
  public function __toString()
  {
    return '#'.$this->id;
  }
  
  // what name is to be printed on the ticket for its "category"
  public function getCategory()
  {
    $name = $this->Gauge->Workspace->on_ticket ? $this->Gauge->Workspace->on_ticket : (string)$this->Gauge;
    if ( !$this->Transaction->HoldTransaction->isNew() )
      $name = $this->Transaction->HoldTransaction->Hold->on_ticket;
    return $name;
    
  }
}
