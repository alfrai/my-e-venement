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

/**
 * SeatedPlan form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SeatedPlanForm extends BaseSeatedPlanForm
{
  public function doSave($con = NULL)
  {
    foreach ( array('Picture' => array('width' => 1024, 'height' => '1200'), 'OnlinePicture' => array('width' => 500, 'height' => '400')) as $picform_name => $dimensions )
    {
      $file = $this->values[$picform_name]['content_file'];
      unset($this->values[$picform_name]['content_file']);
      
      if (!( $file instanceof sfValidatedFile ))
        unset($this->embeddedForms[$picform_name]);
      else
      {
        // data translation
        $this->values[$picform_name]['content']  = base64_encode(file_get_contents($file->getTempName()));
        $this->values[$picform_name]['name']     = $file->getOriginalName();
        $this->values[$picform_name]['type']     = $file->getType();
        $this->values[$picform_name]['width']    = $dimensions['width'];
        $this->values[$picform_name]['height']   = $dimensions['height'];
        
        $this->values['updated_at'] = date('Y-m-d H:i:s'); // this is a hack to force root object update
      }
    }
    
    return parent::doSave($con);
  }
  
  public function configure()
  {
    // pictures & co
    foreach ( array('picture_id' => 'Picture', 'online_picture_id' => 'OnlinePicture') as $field => $rel )
    {
      $this->embedRelation($rel);
      foreach ( array('name', 'type', 'version', 'height', 'width',) as $fieldName )
        unset($this->widgetSchema[$rel][$fieldName], $this->validatorSchema[$rel][$fieldName]);
      $this->validatorSchema[$rel]['content_file']->setOption('required',false);
      unset($this->widgetSchema[$field], $this->validatorSchema[$field]);
    }
    
    $this->widgetSchema['location_id']
      ->setOption('query', Doctrine::getTable('Location')->createQuery()->andWhere('place = ?',true))
      ->setOption('order_by', array('rank, name',''));
    
    $this->widgetSchema   ['workspaces_list']
      ->setOption('query', $q = Doctrine::getTable('Workspace')->createQuery('ws')->andWhere('ws.seated = ?',true)->orderBy('ws.name'));
    if ( $this->object->id && $this->object->location_id ) // VERRRRY IMPORTANT TO AVOID MIS-ROUTING GAUGES IN TICKETTING
      $q->andWhere('ws.id NOT IN (SELECT spws.workspace_id FROM SeatedPlanWorkspace spws LEFT JOIN spws.SeatedPlan spwssp WHERE spwssp.location_id = ? AND spws.seated_plan_id != ?)', array($this->object->location_id, $this->object->id));
    $this->validatorSchema['workspaces_list']->setOption('query', $q);
  }
}
