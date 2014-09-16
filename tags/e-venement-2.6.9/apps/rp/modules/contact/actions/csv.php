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
*    Copyright (c) 2011 Ayoub HIDRI <ayoub.hidri AT gmail.com>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $criterias = $this->getUser()->getAttribute('contact.filters', $this->configuration->getFilterDefaults(), 'admin_module');
    
    // get personal parameters for extractions
    $params = OptionCsvForm::getDBOptions();
    
    $q = $this->buildQuery();
    $a = $q->getRootAlias();
    $q->select   ("$a.title, $a.name, $a.firstname, $a.address, $a.postalcode, $a.city, $a.country, $a.npai, $a.email, $a.description")
      ->leftJoin('o.Category oc')
      ->addSelect("oc.name AS organism_category, o.name AS organism_name")
      ->addSelect('p.department AS professional_department, p.contact_number AS professional_number, p.contact_email AS professional_email')
      ->addSelect('pt.name AS professional_type_name, p.name AS professional_name')
      ->addSelect("o.address AS organism_address, o.postalcode AS organism_postalcode, o.city AS organism_city, o.country AS organism_country, o.email AS organism_email, o.url AS organism_url, o.npai AS organism_npai, o.description AS organism_description")
      ->orderBy("$a.name, $a.firstname");
    
    // phonembers
    if ( in_array('phonename',$params['field']) )
      $q->addSelect("(SELECT tmp1.name FROM ContactPhonenumber tmp1 WHERE tmp1.contact_id = $a.id ORDER BY tmp1.updated_at LIMIT 1) AS phonename");
    else
      $q->addSelect("'' AS phonename");
    if ( in_array('phonenumber',$params['field']) )
      $q->addSelect("(SELECT tmp2.number FROM ContactPhonenumber tmp2 WHERE tmp2.contact_id = $a.id ORDER BY tmp2.updated_at LIMIT 1) AS phonenumber");
    else
      $q->addSelect("'' AS phonenumber");
    if ( in_array('organism_phonename',$params['field']) )
      $q->addSelect("(SELECT tmp3.name FROM OrganismPhonenumber tmp3 WHERE tmp3.organism_id = o.id ORDER BY tmp3.name, tmp3.updated_at LIMIT 1) AS organism_phonename");
    else
      $q->addSelect("'' AS organism_phonename");
    if ( in_array('organism_phonenumber',$params['field']) )
      $q->addSelect("(SELECT tmp4.number FROM OrganismPhonenumber tmp4 WHERE tmp4.organism_id = o.id ORDER BY tmp4.name, tmp4.updated_at LIMIT 1) AS organism_phonenumber");
    else
      $q->addSelect("'' AS organism_phonenumber");
    
    // groups
    if ( in_array('__Groups__name', $params['field']) || in_array('__Professionals__Organism__Groups__name', $params['field']) || in_array('__Professionals__Groups__name', $params['field']) )
      $q->addSelect('p.id, o.id');
    if ( in_array('__Groups__name', $params['field']) )
      $q->leftJoin("$a.Groups ggc")
        ->addSelect('ggc.id, ggc.name');
    if ( in_array('__Professionals__Organism__Groups__name', $params['field']) )
      $q->leftJoin('o.Groups ggo')
        ->addSelect('ggo.id, ggo.name');
    if ( in_array('__Professionals__Groups__name', $params['field']) )
      $q->leftJoin('p.Groups ggp')
        ->addSelect('ggp.id, ggp.name');
    
    // only when groups are a part of filters
    if ( in_array("LEFT JOIN $a.Groups gc",$q->getDqlPart('from')) )
      $q->leftJoin(" p.ProfessionalGroups mpg ON mpg.group_id = gp.id AND mpg.professional_id = p.id")
        ->leftJoin("$a.ContactGroups      mcg ON mcg.group_id = gc.id AND mcg.contact_id      = $a.id")
        ->addSelect("(CASE WHEN mcg.information IS NOT NULL THEN mcg.information ELSE mpg.information END) AS information")
        ->addSelect('mpg.*, p.id, mcg.*')
      ;
    
    $this->lines = $q->fetchArray();
    
    foreach ( $this->lines as $key => $line )
    {
      // check if it's in a group (as a professional) because of a link to an organism or not
      $groups_pro = array();
      $group_pro = false;
      if ( $criterias['groups_list'] )
      {
        foreach ( $line['Professionals'] as $pro )
        foreach ( $pro['ProfessionalGroups'] as $group )
          $groups_pro[$group['group_id']] = $group;
        foreach ( $criterias['groups_list'] as $grpid )
        {
          $group_pro = isset($groups_pro[$grpid]);
          if ( $group_pro )
            break;
        }
      }
      
      // searching into subobjects for nested information
      foreach ( $params['field'] as $field )
      if ( substr($field,0,2) === '__' )
      {
        $fields = explode('__',$field);
        unset($fields[0]);
        $this->lines[$key][$field] = OptionCsvForm::getImplodedData($line, array_values($fields));
      }
      
      // removing professionals objects to get a flat array
      unset($this->lines[$key]['Groups'], $this->lines[$key]['Professionals'], $this->lines[$key]['ContactGroups']);
      
      // empty-ing links to professionals and organisms if not needed
      if ( !$this->filters->showProfessionalData() && !$group_pro )
      foreach ( $line as $field => $value )
      if ( strpos($field,'professional_') !== false || strpos($field,'organism_') !== false )
        $this->lines[$key][$field] = '';
    }
    
    $this->options = array(
      'ms'        => in_array('microsoft',$params['option']),    // microsoft-compatible extraction
      'tunnel'    => in_array('tunnel',$params['option']),       // tunnel effect on fields to prefer organism fields when they exist
      'noheader'  => in_array('noheader',$params['option']),     // no header
      'fields'    => $params['field'],
    );
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset = sfConfig::get('software_internals_charset');
    
    if ( !$request->hasParameter('debug') )
      sfConfig::set('sf_web_debug', false);
    if ( !isset($labels) || !$labels )
    {
      sfConfig::set('sf_escaping_strategy', false);
      $confcsv = sfConfig::get('software_internals_csv'); if ( isset($confcsv['set_charset']) && $confcsv['set_charset'] ) sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    }
    
    if ( $request->hasParameter('debug') )
    {
      $this->getResponse()->sendHttpHeaders();
      $this->setLayout('layout');
    }
    
