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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2011 Ayoub HIDRI <ayoub.hidri AT gmail.com>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php 
    // lots of the lines above came directly from e-venement v1.10 with only few modifications
    
    // options
    $this->params = OptionLabelsForm::getDBOptions();
    $this->fields = OptionCsvForm::getDBOptions();
    $tunnel = true; //in_array('tunnel',$this->fields['option']);
    $this->fields = $this->fields['field'];
    
    if ( !$request->hasParameter('limit') || !$request->hasParameter('go') )
    {
      $this->offset = $request->getParameter('offset', 0);
      $this->limit  = ceil($request->getParameter('limit',sfConfig::get('app_options_labels_offset', 500))/(intval($this->params['nb-x'])*intval($this->params['nb-y'])))*intval($this->params['nb-x'])*intval($this->params['nb-y']); // limit to avoid reaching the PHP memory_limit generating PDF files
      $this->stop   = $this->offset+$this->limit > $this->buildQuery()->offset($this->offset)->limit($this->limit)->count();
      
      $request->setParameter('limit', $this->limit);
      $this->setLayout('nude');
      $this->setTemplate('labelsHub');
      return;
    }
    
    // get back data for labels
    $this->executeCsv($request,true);
    
    // format data for the specific labels' view
    $contacts = $this->lines;
    unset($this->lines);
    
    $this->labels = array(  // the whole bundle of labels
      /*
      array(          // the pages
        array(        // the lines
          array(),    // the labels themselves
        ),
      ),
      */
    );
    for ( $i = 0 ; $i < count($contacts) ; $i++ )
    {
      $contact = $contacts[$i];
      
      // cleaning unwanted fields from contact array
      if ( count($this->fields) > 0 )
      {
        $tmp = array();
        foreach( $contact as $field => $value )
          $tmp[$field] = '';
        foreach ( $this->fields as $name => $value )
          $tmp[$value] = isset($contact[$value]) ? $contact[$value] : '';
        $contact = $tmp;
      }
      
      // tunneling effect
      if ( $tunnel )
        $contact = OptionCsvForm::tunnelingContact($contact);
      
      // make pages
      if ( $i % (intval($this->params['nb-x'])*intval($this->params['nb-y'])) == 0 )
        $this->labels[] = array();
      $nbpages = count($this->labels);
    
      // make lines
      if ( $i % intval($this->params['nb-x']) == 0 )
        $this->labels[$nbpages-1][] = array();
      $nblines = count($this->labels[$nbpages-1]);
    
      $this->labels[$nbpages-1][$nblines-1][] = $contact;
    }
    
    if ( !$request->hasParameter('debug') )
      sfConfig::set('sf_debug', false);
    
    $this->setLayout(false);
