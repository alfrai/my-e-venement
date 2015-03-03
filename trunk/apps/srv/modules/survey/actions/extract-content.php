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
$this->getContext()->getConfiguration()->loadHelpers('I18N');
$survey = $this->form->getObject();
$this->lines = array('title' => array(), 'details' => array());
$queries = new Doctrine_Collection('SurveyQuery');

// main header
$this->lines['title']['name']         = (string)$survey;
$this->lines['title']['professional'] = '';
$this->lines['title']['organism']     = '';
$this->lines['title']['transaction']  = '';
foreach ( $this->survey->Queries as $query )
{
  foreach ( $query->Options as $option )
    $this->lines['title'][$query->slug.'-'.$option->id] = '';
  $this->lines['title'][$query->slug.($query->Options->count() > 0 ? '-'.$query->Options[0]->id : '')] = (string)$query;
  $queries[$query->id] = $query;
}

// second header
$this->lines['details']['name']         = __('Contact');
$this->lines['details']['professional'] = __('Professional');
$this->lines['details']['organism']     = __('Organism');
$this->lines['details']['transaction']  = __('Transaction');
foreach ( $this->survey->Queries as $query )
{
  $this->lines['details'][$query->slug] = '';
  foreach ( $query->Options as $option )
    $this->lines['details'][$query->slug.'-'.$option->id] = $option->value;
}

// lines
$i = 0;
foreach ( $this->survey->AnswersGroups as $group )
{
  $this->lines[$i] = array();
  $this->lines[$i]['name'] = (string)$group->Contact;
  $this->lines[$i]['professional'] = (string)$group->Professional->name_type;
  $this->lines[$i]['organism'] = (string)$group->Professional->Organism;
  $this->lines[$i]['transaction'] = '#'.$group->transaction_id;
  
  foreach ( $group->Answers as $answer )
  {
    if ( $queries[$answer->survey_query_id]->Options->count() == 0 )
      $this->lines[$i][$queries[$answer->survey_query_id]->slug] = $answer->value;
    else
    {
      $this->lines[$i][$queries[$answer->survey_query_id]->slug] = '';
      foreach ( $queries[$answer->survey_query_id]->Options as $option ) // init
      if ( !isset($this->lines[$i][$queries[$answer->survey_query_id]->slug.'-'.$option->id]) )
        $this->lines[$i][$queries[$answer->survey_query_id]->slug.'-'.$option->id] = '';
      foreach ( $queries[$answer->survey_query_id]->Options as $option ) // real
      if ( $option->value == $answer->value )
        $this->lines[$i][$queries[$answer->survey_query_id]->slug.'-'.$option->id] = $answer->value;
    }
  }
  $i++;
}

$this->options = array(
 'ms'        => in_array('microsoft',$params['option']),    // microsoft-compatible extraction
 'fields'    => array_keys($this->lines['title']),
 'class'     => 'Contact',
 'noheader'  => true,
);

$this->outstream = 'php://output';
$this->delimiter = $this->options['ms'] ? ';' : ',';
$this->enclosure = '"';
$this->charset = sfConfig::get('software_internals_charset');

sfConfig::set('sf_escaping_strategy', false);
