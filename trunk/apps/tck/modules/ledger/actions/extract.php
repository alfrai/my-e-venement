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
    $this->charset   = sfContext::getInstance()->getConfiguration()->charset;
    
    sfConfig::set('sf_escaping_strategy', false);
    sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
    if ( $this->getContext()->getConfiguration()->getEnvironment() == 'dev' )
    {
      $this->getResponse()->sendHttpHeaders();
      $this->setLayout('layout');
    }
    else
      sfConfig::set('sf_web_debug', false);
    
    switch ( $request->getParameter('type','cash') ) {
    case 'sales':
      $this->executeSales($request);
      return 'Sales';
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
          'value'           => (string) $payment->value,
          'reference'       => $method->account,
          'transaction_id'  => '#'.$payment->transaction_id,
          'contact'         => (string)( $payment->Transaction->professional_id ? $payment->Transaction->Professional : $payment->Transaction->Contact ),
          'date'            => format_date($payment->created_at),
          'user'            => (string)$payment->User,
        );
      
      return 'Cash';
      break;
    }
