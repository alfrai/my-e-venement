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
    $q->select   ("$a.title, $a.name, $a.firstname, $a.address, $a.postalcode, $a.city, $a.country, $a.npai, $a.email")
      ->addSelect("(SELECT tmp.name FROM ContactPhonenumber tmp WHERE contact_id = $a.id ORDER BY updated_at LIMIT 1) AS phonename")
      ->addSelect("(SELECT tmp2.number FROM ContactPhonenumber tmp2 WHERE contact_id = $a.id ORDER BY updated_at LIMIT 1) AS phonenumber")
      ->addSelect("$a.description")
      ->leftJoin('o.Category oc')
      ->addSelect("oc.name AS organism_category, o.name AS organism_name")
      ->addSelect('p.department AS professional_department, p.contact_number AS professional_number, p.contact_email AS professional_email')
      ->addSelect('pt.name AS professional_type_name, p.name AS professional_name')
      ->addSelect("o.address AS organism_address, o.postalcode AS organism_postalcode, o.city AS organism_city, o.country AS organism_country, o.email AS organism_email, o.url AS organism_url, o.npai AS organism_npai, o.description AS organism_description")
      ->addSelect("(SELECT tmp3.name   FROM OrganismPhonenumber tmp3 WHERE organism_id = $a.id ORDER BY updated_at LIMIT 1) AS organism_phonename")
      ->addSelect("(SELECT tmp4.number FROM OrganismPhonenumber tmp4 WHERE organism_id = $a.id ORDER BY updated_at LIMIT 1) AS organism_phonenumber");
    
    // only when groups are a part of filters
    if ( in_array("LEFT JOIN $a.Groups gc",$q->getDqlPart('from')) )
      $q->leftJoin(" p.ProfessionalGroups mp ON mp.group_id = gp.id AND mp.professional_id = p.id")
        ->leftJoin("$a.ContactGroups      mc ON mc.group_id = gc.id AND mc.contact_id     = $a.id")
        ->addSelect("(CASE WHEN mc.information IS NOT NULL THEN mc.information ELSE mp.information END) AS information");
    $this->lines = $q->fetchArray();
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms'        => in_array('microsoft',$params['option']),    // microsoft-compatible extraction
      'tunnel'    => in_array('tunnel',$params['option']),       // tunnel effect on fields to prefer organism fields when they exist
      'noheader'  => in_array('noheader',$params['option']),     // no header
      'fields'    => $params['field'],
    );
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfContext::getInstance()->getConfiguration()->charset;
    
    if ( !$request->hasParameter('debug') )
      sfConfig::set('sf_web_debug', false);
    if ( !isset($labels) || !$labels )
    {
      sfConfig::set('sf_escaping_strategy', false);
      sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    }
    
    if ( $request->hasParameter('debug') )
      $this->setLayout(true);
    else
    {
      $this->getResponse()->setContentType('text/comma-separated-values');
      $this->getResponse()->setHttpHeader('Content-Disposition', "attachment; filename=contacts.csv");
      $this->getResponse()->sendHttpHeaders();
    }
