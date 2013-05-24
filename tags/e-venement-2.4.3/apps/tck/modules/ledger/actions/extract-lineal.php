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
  // loading the formats
  require_once sfContext::getInstance()->getConfigCache()
    ->checkConfig('modules/ledger/config/formats.yml',true);
  
  // for the action
  $class  = sfConfig::get('formats_lineal_class');
  $method = sfConfig::get('formats_lineal_method','createQuery');
  $criterias = sfConfig::get('formats_lineal_criterias');
  $extra_fields = sfConfig::get('formats_lineal_extra_fields');
  
  // extra fields
  $this->extrafields = array();
  if ( isset($extra_fields['content']) )
    $this->extrafields = $extra_fields['content'];
  if ( isset($extra_fields['class']) )
  {
    foreach ( Doctrine_Query::create()
      ->from($extra_fields['class'].' t')
      ->fetchArray() as $arr )
      $this->extrafields[$arr['name']] = $arr['value'];
  }
  
  // for the renderer
  $this->fields_description = sfConfig::get('formats_lineal_fields_description',array());
  $this->lines = sfConfig::get('formats_lineal_lines',array());
  $this->meta_format = sfConfig::get('formats_lineal_meta_format',array('decorator' => '', 'separator' => ''));
  
  // constructing data
  $this->criterias = $this->formatCriterias($request);
  $q = Doctrine::getTable($class)->$method($alias = 't');
  
  // filtering on subobjects
  if (!( isset($criterias['subobjects']) && is_array($criterias['subobjects']) ))
    $criterias['subobjects'] = array();
  if ( isset($criterias['fields']) )
    $criterias['subobjects'][] = array('fields' => $criterias['fields'], 'alias' => $alias);
  foreach ( $criterias['subobjects'] as $subobject => $params )
  {
    //$q->orWhere('true'); // TO BE CONFIRMED, but it seems this OR condition was a bad idea...
    foreach ( $params['fields'] as $criteria => $field )
    {
      if ( !isset($this->criterias[$criteria]) )
        continue;
      
      if ( is_array($this->criterias[$criteria]) )
      {
        switch ( $field['match'] ) {
        case 'integer':
          $q->andWhere(sprintf('%s.%s >= ? AND %s.%s < ?',$params['alias'],$field['field'],$params['alias'],$field['field']),$this->criterias[$criteria]);
          break;
        case 'in':
          $q->andWhereIn(sprintf('%s.%s',$params['alias'],$field['field']),$this->criterias[$criteria]);
          break;
        }
      }
      else // if ( isset($criterias['subobjects']) && is_array($criterias['subobjects']) )
        $q->andWhere(sprintf('%s.%s = ?',$params['alias'],isset($field['field']) ? $field['field'] : $field),$this->criterias[$criteria]);
    }
  }
  
  $this->transactions = $q->execute();
