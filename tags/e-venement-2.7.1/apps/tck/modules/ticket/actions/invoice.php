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
    if (!( isset($no_actions) && $no_actions ))
    $this->executeAccounting($request,true,$request->hasParameter('partial') ? (intval($request->getParameter('partial')).'' === $request->getParameter('partial') ? intval($request->getParameter('partial')) : $request->getParameter('manifestation_id')) : false);
    
    $this->partial = false;
    $this->invoice = false;
    if ( $request->hasParameter('partial') && intval($request->getParameter('manifestation_id')) > 0 )
    {
      $this->partial = true;
      foreach ( $this->transaction->Invoice as $key => $invoice )
      if ( $invoice->manifestation_id == intval($request->getParameter('manifestation_id')) )
        $this->invoice = $invoice;
      
      if ( !$this->invoice )
      {
        $this->invoice = new Invoice();
        $this->transaction->Invoice[] = $this->invoice;
      }
      $this->invoice->manifestation_id = intval($request->getParameter('manifestation_id'));
    }
    else
    {
      foreach ( $this->transaction->Invoice as $invoice )
      if ( is_null($invoice->manifestation_id) )
        $this->invoice = $invoice;
      
      if ( !$this->invoice )
        $this->invoice = new Invoice();
      $this->transaction->Invoice[] = $this->invoice;
    }
    
    $this->invoice->updated_at = date('Y-m-d H:i:s');
    $this->invoice->save();
    
    // preparing things for both PDF & HTML
    $this->data = array();
    foreach ( array('transaction', 'nocancel', 'tickets', 'invoice', 'totals', 'partial') as $var )
    if ( isset($this->$var) )
      $this->data[$var] = $this->$var;
    
    if ( !$request->hasParameter('pdf') )
      return 'Success';
    
    $pdf = new sfDomPDFPlugin();
    $pdf->setInput($content = $this->getPartial('invoice_pdf', $this->data));
    $this->getResponse()->setContentType('application/pdf');
    $this->getResponse()->setHttpHeader('Content-Disposition', 'attachment; filename="invoice.pdf"');
    return $this->renderText($pdf->execute());
