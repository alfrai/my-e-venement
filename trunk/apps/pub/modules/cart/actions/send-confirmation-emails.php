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
    // sending emails to contact and organizators
    if ( !sfConfig::has('app_texts_email_confirmation') )
      throw new liOnlineSaleException('You need to configure app_texts_email_confirmation in your apps/pub/config/app.yml file');
    
    /*
    if ( !sfConfig::get('sf_web_debug', false) // if we are not working in a development environment
      && $transaction->id != $action->getUser()->getTransactionId() // and it's not the current transaction
      && !( $transaction->contact_id && $action->getUser()->getTransaction()->contact_id && $transaction->contact_id != $action->getUser()->getTransaction()->contact_id ) // and it's not a current user's transaction
    )
      throw new liOnlineSaleException('You cannot access a transaction that does not belong to you.');
    */
    
    sfApplicationConfiguration::getActive()->loadHelpers(array('Date','Number','I18N', 'Url'));
    
    // command is not yet i18n, only french
    $command = 'Commande #'.$transaction->id;
    if ( $transaction->Contact )
      $command .= ' pour '.$transaction->Contact;
    $command .= "\n";
    
    // tickets
    $tickets = array();
    foreach ( $transaction->Tickets as $ticket )
    {
      if ( !isset($tickets[$ticket->Manifestation->happens_at.' -- '.$ticket->Manifestation->event_id]) ) $tickets[$ticket->Manifestation->happens_at.' -- '.$ticket->Manifestation->event_id] = array();
      if ( !isset($tickets[$ticket->Manifestation->happens_at.' -- '.$ticket->Manifestation->event_id][$ticket->Manifestation->id]) ) $tickets[$ticket->Manifestation->happens_at.' -- '.$ticket->Manifestation->event_id][$ticket->Manifestation->id] = array();
      
      $tickets[$ticket->Manifestation->happens_at.' -- '.$ticket->Manifestation->event_id]['event'] = $ticket->Manifestation->Event;
      $tickets[$ticket->Manifestation->happens_at.' -- '.$ticket->Manifestation->event_id][$ticket->Manifestation->id]['manif'] = $ticket->Manifestation;
      if ( !isset($tickets[$ticket->Manifestation->happens_at.' -- '.$ticket->Manifestation->event_id][$ticket->Manifestation->id][$ticket->price_id]) )
        $tickets[$ticket->Manifestation->happens_at.' -- '.$ticket->Manifestation->event_id][$ticket->Manifestation->id][$ticket->price_id] = array(
          'qty' => 0,
          'price' => $ticket->Price,
          'value' => 0,
          'taxes' => 0,
        );
      $tickets[$ticket->Manifestation->happens_at.' -- '.$ticket->Manifestation->event_id][$ticket->Manifestation->id][$ticket->price_id]['qty']++;
      $tickets[$ticket->Manifestation->happens_at.' -- '.$ticket->Manifestation->event_id][$ticket->Manifestation->id][$ticket->price_id]['value'] += $ticket->value;
      $tickets[$ticket->Manifestation->happens_at.' -- '.$ticket->Manifestation->event_id][$ticket->Manifestation->id][$ticket->price_id]['taxes'] += $ticket->taxes;
    }
    ksort($tickets);
    
    foreach ( $tickets as $event )
    {
      $command .= "\n".$event['event'].": \n";
      unset($event['event']);
      foreach ( $event as $manif )
      {
        $command .= "&nbsp;&nbsp;".__('at')." ".$manif['manif']->getShortenedDate().", ".$manif['manif']->Location.(($sp = $ticket->Manifestation->Location->getWorkspaceSeatedPlan($ticket->Gauge->workspace_id)) ? '*' : '')."\n";
        unset($manif['manif']);
        foreach ( $manif as $tickets )
          $command .= "&nbsp;&nbsp;&nbsp;&nbsp;".($tickets['price']->description ? $tickets['price']->description : $tickets['price'])." x ".$tickets['qty']." = ".format_currency($tickets['value'],'€').'    + '.format_currency($tickets['taxes'],'€').' ('.__('Ticketting fees').")\n";
      }
    }
    
    // products
    $products = array();
    foreach ( $transaction->BoughtProducts as $bp )
    {
      if ( !isset($products[$bp->name]) ) $products[$bp->name] = array();
      if ( !isset($products[$bp->name][$bp->code.'-||-'.$bp->declination]) ) $products[$bp->name][$bp->code.'-||-'.$bp->declination] = array();
      
      $products[$bp->name]['product'] = $bp->name;
      $products[$bp->name][$bp->code.'-||-'.$bp->declination]['declination'] = $bp->declination;
      if ( !isset($products[$bp->name][$bp->code.'-||-'.$bp->declination][$bp->price_name]) )
        $products[$bp->name][$bp->code.'-||-'.$bp->declination][$bp->price_name] = array(
          'qty' => 0,
          'price' => $bp->price_name,
          'value' => 0,
          'taxes' => 0,
        );
      $products[$bp->name][$bp->code.'-||-'.$bp->declination][$bp->price_name]['qty']++;
      $products[$bp->name][$bp->code.'-||-'.$bp->declination][$bp->price_name]['value'] += $bp->value;
    }
    foreach ( $products as $product )
    {
      $command .= "\n".$product['product'].": \n";
      unset($product['product']);
      foreach ( $product as $declination )
      {
        $command .= "&nbsp;&nbsp;".$declination['declination']."\n";
        unset($declination['declination']);
        foreach ( $declination as $bps )
          $command .= "&nbsp;&nbsp;&nbsp;&nbsp;".($bps['price'] ? $bps['price'] : $bps['price'])." x ".$bps['qty']." = ".format_currency($bps['value'],'€')."\n";
      }
    }
    
    // member cards
    if ( $transaction->MemberCards->count() > 0 )
    {
      $command .= "\n";
      $command .= __("Member cards")."\n";
      foreach ( $transaction->MemberCards as $mc )
      $command .= $mc."\n";
    }
    
    // footer
    $command .= "\n";
    $command .= __('Total')."\n";
    $command .= "&nbsp;&nbsp;".__('Tickets').": ".format_currency($transaction->getTicketsPrice(true),'€')."\n";
    $command .= "&nbsp;&nbsp;".__('Store').": ".format_currency($transaction->getProductsPrice(true),'€')."\n";
    if ( $amount = $transaction->getMemberCardPrice(true) )
    $command .= "&nbsp;&nbsp;".__('Member cards').": ".format_currency($amount,'€')."\n";
    if ( sfConfig::get('app_payment_type', 'paybox') != 'onthespot' )
    {
      $command .= "\n";
      $command .= "Paiements\n";
      if ( $mc_amount = $transaction->getTicketsLinkedToMemberCardPrice(true) )
      $command .= "&nbsp;&nbsp;".__('Member cards').": ".format_currency($mc_amount,'€')."\n";
      $command .= "&nbsp;&nbsp;".__('Credit card').": ".format_currency($transaction->getPrice(true,true),'€')."\n";
    }
    
    $replace = array(
      '%%DATE%%' => format_date(date('Y-m-d')),
      '%%CONTACT%%' => (string)$transaction->Contact,
      '%%TRANSACTION_ID%%' => $transaction->id,
      '%%SELLER%%' => sfConfig::get('app_informations_title'),
      '%%COMMAND%%' => $command,
      '%%TICKETS%%' => $transaction->renderSimplifiedTickets(), // HTML tickets w/ barcode
      '%%PRODUCTS%%' => $transaction->renderSimplifiedProducts(array('barcode' => 'png',)), // HTML products w/ barcode
      '%%NOTICES%%' => '* '.sfConfig::get('app_text_email_seated_tickets', __('All lines marked with an wildcard concern a seated venue. You will receive a new email as soon as a change is done in the seat allocation for your tickets.')),
    );
    
    $email = new Email;
    if ( sfConfig::get('app_contact_professional', false) )
      $email->Professionals[] = $transaction->Contact->Professionals[0];
    else
      $email->Contacts[] = $transaction->Contact;
    $email->setType('Order')->addDispatcherParameter('transaction', $transaction);
    $email->field_bcc = sfConfig::get('app_informations_email','admin@libre-informatique.fr');
    $email->field_subject = sfConfig::get('app_informations_title').': '.__('your order #').$transaction->id;
    $email->from_txt = sfConfig::get('app_informations_title','');
    $email->field_from = sfConfig::get('app_informations_email','contact@libre-informatique.fr');
    $email->content = nl2br(str_replace(array_keys($replace),$replace,sfConfig::get('app_texts_email_confirmation')));
    $email->content .= nl2br("\n\n".sfConfig::get('app_texts_email_footer',<<<EOF
--
<a href="http://www.e-venement.net/">e-venement</a> est le système de billetterie informatisée développé par <a href="http://www.libre-informatique.fr/">Libre Informatique</a>. 
Ces logiciels sont distribués sous <a href="http://fr.wikipedia.org/wiki/Licences_libres">licences libres</a>

Libre Informatique
<a href="mailto:contact@libre-informatique.fr">contact@libre-informatique.fr</a>
<a href="http://www.libre-informatique.fr">http://www.libre-informatique.fr</a>
<style type="text/css" media="all">
  .cmd-ticket { page-break-before: always; }
</style>
EOF
    ));
    
    if ( sfConfig::get('app_tickets_pdf_attachments', true) )
    foreach ( array('tickets' => 'renderSimplifiedTickets', 'products' => 'renderSimplifiedProducts') as $var => $fct )
    if ( is_array($$var) && count($$var) > 0 )
    {
      if (!( $content = $transaction->$fct(array('barcode' => 'png')) ))
        continue;
      
      // attachments, tickets/products in PDF
      $pdf = new sfDomPDFPlugin();
      $pdf->setInput($action->getPartial('transaction/get_tickets_pdf', array('tickets_html' => $content)));
      $pdf = $pdf->render();
      file_put_contents(sfConfig::get('sf_upload_dir').'/'.($filename = $var.'-'.$transaction->id.'-'.date('YmdHis').'-'.rand(1000000000,9999999999).'.pdf'), $pdf);
      $attachment = new Attachment;
      $attachment->filename = $filename;
      $attachment->original_name = $filename;
      $email->Attachments[] = $attachment;
      $attachment->save();
    }
    
    $action->dispatcher->notify(new sfEvent($action, 'email.before_sending_tickets', $email->getDispatcherParameters() + array('email' => $email)));
    $action->dispatcher->notify(new sfEvent($action, 'email.before_sending_products', $email->getDispatcherParameters() + array('email' => $email)));
    $email->isATest(false);
    $email->setNoSpool();
    return $email->save();
