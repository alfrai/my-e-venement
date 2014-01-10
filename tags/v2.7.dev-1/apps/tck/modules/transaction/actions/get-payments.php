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
  /**
   * function executeGetManifestations
   * @param sfWebRequest $request, given by the framework (required: id, optional: Array|int manifestation_id || (price_id, gauge_id, printed))
   * @return ''
   * @display a json array containing :
   * json:
   * error:
   *   0: boolean true if errorful, false else
   *   1: string explanation
   * success:
   *   success_fields:
   *     payments:
   *       data:
   *         type: payments
   *         reset: boolean
   *         content: Array (see below)
   *   error_fields: only if any error happens
   *     payments: string explanation
   *
   * the data Array is :
   *   [payment_id]: integer
   *     id: integer
   *     value: float
   *     method: string
   *     payment_method_id: integer
   *     date: string (PGSQL format)
   *     delete_url: string URL
   **/

  $this->transaction = false;
  if ( $request->getParameter('id',false) )
  {
    $this->transaction = Doctrine::getTable('Transaction')->createQuery('t')
      ->andWhere('t.id = ?', $request->getParameter('id'))
      ->leftJoin('t.Payments p')
      ->fetchOne();
  }
  
  $this->json = array();
  if ( !$this->transaction )
    return;
  
  foreach ( $this->transaction->Payments as $payment )
  {
    $this->json[$payment->id] = array(
      'id'            => $payment->id,
      'value'         => $payment->value,
      'method'        => (string)$payment->Method,
      'payment_method_id' => $payment->Method->id,
      'date'          => $payment->created_at,
      'delete_url'    => cross_app_url_for('tck','transaction/complete?id='.$this->transaction->id, true),
    );
  }
  
  $this->json = array(
    'error' => array(false, ''),
    'success' => array(
      'success_fields' => array(
        'payments' => array(
          'data' => array(
            'type' => 'payments',
            'reset' => 1,
            'content' => $this->json,
          ),
        ),
      ),
      'error_fields' => array(),
    ),
  );
