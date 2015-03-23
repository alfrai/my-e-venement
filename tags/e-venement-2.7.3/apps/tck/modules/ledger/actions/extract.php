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
    $this->getContext()->getConfiguration()->loadHelpers('Date');
    
    $criterias = $this->formatCriterias($request);
    $this->dates = $criterias['dates'];
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => in_array('microsoft',$params['option']),
      'tunnel' => false,
      'noheader' => false,
    );
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfConfig::get('software_internals_charset');
    
    sfConfig::set('sf_escaping_strategy', false);
    $confcsv = sfConfig::get('software_internals_csv'); if ( isset($confcsv['set_charset']) && $confcsv['set_charset'] ) sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
    if ( $this->getContext()->getConfiguration()->getEnvironment() == 'dev' && $request->hasParameter('debug') )
    {
      $this->getResponse()->sendHttpHeaders();
      $this->setLayout('layout');
    }
    else
      sfConfig::set('sf_web_debug', false);
    
    switch ( $request->getParameter('type','cash') ) {
    case 'sales':
      $this->executeSales($request);
      $this->lines = array();
      $this->options['fields'] = array(
        'event', 'manifestation', 'location',
        'price', 'user', 'qty',
        'pit', 'vat', 'tep',
        'account',
      );
      
      foreach ( $this->events as $event )
      foreach ( $event->Manifestations as $manif )
      if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) )
      foreach ( $manif->Tickets as $ticket )
      {
        if ( !isset($this->lines[$key = 'e'.$event->id.'m'.$manif->id.'p'.$ticket->price_id.'u'.$ticket->sf_guard_user_id.($ticket->cancelling ? 'a' : '')]) )
          $this->lines[$key] = array(
            'event'         => (string)$event,
            'manifestation' => (string)$manif,
            'location'      => (string)$manif->Location,
            'price'         => (string)$ticket->Price,
            'user'          => (string)$ticket->User,
            'qty'           => 0,
            'pit'           => 0,
            'vat'           => 0,
            'tep'           => 0,
            'account'       => $event->accounting_account,
          );
        $this->lines[$key]['qty'] += $ticket->cancelling ? -1 : 1;
        $this->lines[$key]['pit'] += $ticket->value;
        $this->lines[$key]['vat'] += $tmp = round($ticket->value - $ticket->value / (1+$ticket->vat),2);
        $this->lines[$key]['tep'] += $ticket->value - $tmp;
      }
      else
      {
        $infos = $manif->getInfosTickets($sf_data->getRaw('options'));
        if ( !isset($this->lines[$key = 'e'.$event->id.'m'.$manif->id]) )
          $this->lines[$key] = array(
            'event'         => (string)$event,
            'manifestation' => (string)$manif,
            'location'      => (string)$manif->Location,
            'price'         => '',
            'user'          => '',
            'qty'           => $infos[$manif->id]['qty'],
            'pit'           => $infos[$manif->id]['value'],
            'vat'           => 0,
            'tep'           => $infos[$manif->id]['value'],
            'account'       => $event->accounting_account,
          );
        foreach ( $infos[$manif->id]['vat'] as $rate => $amount )
        {
          $this->lines[$key]['vat'] += $tmp = round($amount,2);
          $this->lines[$key]['tep'] -= $tmp;
        }
      }
      return 'Sales';
      break;
    case 'lineal':
      require(dirname(__FILE__).'/extract-lineal.php');
      return 'Lineal';
      break;
    default:
      $this->options['fields'] = array(
        'method',
        'value',
        'account',
        'transaction_id',
        'contact',
        'date',
        'user',
      );
      $this->executeCash($request);
      
      $this->lines = array();
      foreach ( $this->methods as $method )
      foreach ( $method->Payments as $payment )
        $this->lines[] = array(
          'method'          => (string) $method,
          'value'           => (string) $payment->weight_value,
          'reference'       => $method->account,
          'transaction_id'  => '#'.$payment->transaction_id,
          'contact'         => (string)( $payment->Transaction->professional_id ? $payment->Transaction->Professional : $payment->Transaction->Contact ),
          'date'            => format_date($payment->created_at),
          'user'            => (string)$payment->User,
        );
      
      return 'Cash';
      break;
    }
