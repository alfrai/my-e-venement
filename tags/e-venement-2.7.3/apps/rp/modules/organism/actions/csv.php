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
    $q = $this->buildQuery();
    $a = $q->getRootAlias();
    $q->select("$a.name AS organism_name, $a.address AS organism_address, $a.postalcode AS organism_postalcode, $a.city AS organism_city, $a.country AS organism_city, $a.country AS organism_country, $a.email AS organism_email, $a.url AS organism_url, $a.npai AS organism_npai, $a.description AS organism_description")
      ->leftJoin("$a.CloseContact cc")
      ->leftJoin('cc.Contact ccc')
      ->leftJoin('cc.ProfessionalType cct')
      ->addSelect("oc.name AS organism_category")
      ->addSelect('ccc.title AS cc_title, ccc.name AS cc_name, ccc.firstname AS cc_firstname, cct.name AS cc_type, cc.name AS cc_function, cc.contact_number AS cc_phonenumber, cc.contact_email AS cc_email')
      ->addSelect("(SELECT tmp3.name   FROM OrganismPhonenumber tmp3 WHERE organism_id = $a.id ORDER BY updated_at LIMIT 1) AS organism_phonename")
      ->addSelect("(SELECT tmp4.number FROM OrganismPhonenumber tmp4 WHERE organism_id = $a.id ORDER BY updated_at LIMIT 1) AS organism_phonenumber");
    
    if ( $labels )
      $q->limit($request->getParameter('limit', 500))
        ->offset($request->getParameter('offset', 0));
    
    $this->lines = $q->fetchArray();
    
    $params = OptionCsvForm::getDBOptions();
    if ( in_array('professional_important', $params['field']) )
    {
      unset($params['field'][$s=array_search('professional_important', $params['field'])]);
      $params['field'][] = 'cc_title';
      $params['field'][] = 'cc_name';
      $params['field'][] = 'cc_firstname';
      $params['field'][] = 'cc_type';
      $params['field'][] = 'cc_function';
      $params['field'][] = 'cc_phonenumber';
      $params['field'][] = 'cc_email';
    }
    foreach ( $params['field'] AS $key => $name )
    if ( substr($name,0,9) != 'organism_' && substr($name,0,3) != 'cc_' )
      unset($params['field'][$key]);
    $this->options = array(
      'ms'        => in_array('microsoft',$params['option']),    // microsoft-compatible extraction
      'noheader'  => in_array('noheader',$params['option']),     // no header
      'fields'    => $params['field'],
      'class'     => 'Organism',
    );
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfConfig::get('software_internals_charset');
    
    if ( !$request->hasParameter('debug') )
      sfConfig::set('sf_web_debug', false);
    if ( !isset($labels) || !$labels )
    {
      sfConfig::set('sf_escaping_strategy', false);
      $confcsv = sfConfig::get('software_internals_csv'); if ( isset($confcsv['set_charset']) && $confcsv['set_charset'] ) sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    }
    
    if ( $request->hasParameter('debug') )
    {
      $this->setLayout('layout');
      $this->getResponse()->sendHttpHeaders();
    }
