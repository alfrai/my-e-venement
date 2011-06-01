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
*    Copyright (c) 2006-2008 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
global $bd, $id, $user, $action, $actions;
if ( !isset($id) ) $id = $_GET['id'];
require_once('conf.inc.php');

if ( $config['evt']['ext']['checklist'] )
{
  includeClass('bdRequest');
  includeLib('ttt');
  includeLib("actions");
  
  if ( $action != $actions['view'] )
    includeJS('checklist','evt');
?>
<?php
  // ajout / modif des checkpoints
  if ( is_array($checklist = $_POST['check']) && $user->evtlevel >= $config['evt']['right']['mod'] )
  foreach ( $checklist as $checkid => $check )
  {
    if ( $check['remove'] && intval($checkid) > 0 )
    {
      if ( !$bd->delRecordsSimple('checklist',array('id' => $checkid)) )
        $user->addAlert('Impossible de supprimer le checkpoint '.$check['checkpoint']['value']);
    }
    elseif ( $check['checkpoint'] )
    {
      $model = array();
      $model['evtid'] = $id;
      $model['checkpoint'] = $check['checkpoint']['value'];
      $model['description'] = $check['description']['value'];
      if ( $model['checkpoint'] != '' )
      {
        if ( intval($checkid) > 0 )
        {
          if ( !$bd->updateRecordsSimple('checklist',array('id' => intval($checkid)),$model) )
            $user->addAlert('Impossible de mettre à jour le checkpoint '.$check['checkpoint']['value']);
        }
        else
        {
          $model['owner'] = $user->getId();
          $model['done']  = $check['done']['value']  == 'yes' ? date('Y-m-d H:i:s') : NULL;
          $model['doing'] = $check['doing']['value'] == 'yes' ? date('Y-m-d H:i:s') : NULL;
          $model['isfile']  = $check['isfile']['value'] == 'yes' ? 't' : 'f';
          if ( !$bd->addRecord('checklist',$model) )
            $user->addAlert("Impossible d'ajouter le checkpoint ".$check['checkpoint']['value']);
        }
      }
    }
  
    $checks = array();
    
    // les MàJ des status
    foreach ( $_POST['check'] as $checkid => $check )
    if ( intval($checkid) > 0 )
    for ( $i = 0 ; $i < 2 ; $i++ )
    {
      $fields = array('done','doing');
      $nberr = 0;
      $data = array(
        'modifier'  => $user->getId(),
        $fields[$i] => $check[$fields[$i]]['value']  == 'yes' ? date('Y-m-d H:i:s') : NULL,
      );
      $cond = array(
        'id'    => intval($checkid),
        'evtid' => $id,
        $fields[$i] => $check[$fields[$i]]['value']  == 'yes' ? NULL : true,
      );
      $r = $bd->updateRecordsSimple('checklist',$cond,$data);
      $checks[] = $checkid;
      if ( $r === false )
        $nberr++;
      if ( $nberr > 0 )
        $user->addAlert('Impossible de mettre à jour la date de validation de '.$nberr.' checkpoint(s).');
    }
    
    // ceux qui ne le sont pas
    $bd->updateRecords('checklist', (count($checks) > 0 ? 'id NOT IN ('.implode(',',$checks).') AND ' : '').'evtid = '.$id, array('done' => NULL, 'doing' => NULL));
  }
?>
<?php
  $query = ' SELECT c.id, c.checkpoint, c.description, c.done, c.doing, c.isfile, o.name AS owner, m.name AS modifier
             FROM checklist c
             LEFT JOIN account o ON o.id = c.owner
             LEFT JOIN account m ON m.id = c.modifier
             WHERE evtid = '.$id.'
             ORDER BY c.checkpoint';
  $checklist = new bdRequest($bd,$query);
  $check = array();
  if ( $checklist->countRecords() > 0 || $action != $actions['view'] ):
?>
  <ul class="<?php echo $action == $actions['view'] ? 'view' : 'mod' ?>" id="checklist">
  <?php for ( $i = 0 ; $action != $actions['view'] && $i == 0 || $check = $checklist->getRecordNext() ; $i++ ): ?>
    <?php
      if ( !$check )
        $check = array('id' => 0);
      else
        $check['id'] = intval($check['id']);
    ?>
    <li>
      <span class="<?php echo $name = 'checkpoint' ?>" <?php if ( $action == $actions['view'] ) echo 'title="'.htmlsecure($check['description']).'"' ?>>
        <?php if ( $action == $actions['view'] && $check['isfile'] == 't' ): ?>
        <a href="file://<?php echo htmlsecure($check[$name]) ?>">link to file</a>
        <?php else: ?>
        <?php printField('check['.$check['id'].']['.$name.']',$check[$name],null,255); ?>
        <?php endif; ?>
      </span>
      <span class="<?php echo $name = 'doing' ?>" title="Pris en Charge, date: <?php echo strtotime($check[$name]) > 0 ? htmlsecure(date('d/m/Y H:i',strtotime($check[$name]))) : '' ?>">
        <label for="check[<?php echo $check['id'] ?>][<?php echo $name ?>][value]">PeC</label>
        <input type="checkbox" name="check[<?php echo $check['id'] ?>][<?php echo $name ?>][value]" value="yes" <?php echo $check[$name] ? 'checked="checked"' : '' ?> />
      </span>
      <span class="<?php echo $name = 'done' ?>" title="Terminé, date: <?php echo strtotime($check[$name]) > 0 ? htmlsecure(date('d/m/Y H:i',strtotime($check[$name]))) : '' ?>">
        <label for="check[<?php echo $check['id'] ?>][<?php echo $name ?>][value]">fait</label>
        <input type="checkbox" name="check[<?php echo $check['id'] ?>][<?php echo $name ?>][value]" value="yes" <?php echo $check[$name] ? 'checked="checked"' : '' ?> />
      </span>
      <?php if ( $action != $actions['view'] && $check['id'] == 0 ): ?>
      <span class="<?php echo $name = 'isfile' ?>" title="Ceci est l'adresse d'un fichier ?">
        <label for="check[<?php echo $check['id'] ?>][<?php echo $name ?>][value]">fichier?</label>
        <input type="checkbox" name="check[<?php echo $check['id'] ?>][<?php echo $name ?>][value]" value="yes" <?php echo $check[$name] ? 'checked="checked"' : '' ?> />
      </span>
      <?php endif; ?>
      <?php if ( $action != $actions['view'] && $check['id'] > 0 ): ?>
      <span class="remove">
        <label for="check[<?php echo $check['id'] ?>][remove][value]">suppr.</label>
        <input type="checkbox" name="check[<?php echo $check['id'] ?>][remove][value]" value="yes" />
      </span>
      <?php endif; ?>
      <span class="<?php echo $name = 'description' ?>"><span>
        <?php printField('check['.$check['id'].']['.$name.']',$check[$name],null,4,40,true); ?>
      </span></span>
      <span class="<?php echo $name = 'owner' ?>">Créateur: <?php echo htmlsecure($check[$name]) ?></span>
      <span class="<?php echo $name = 'modifier' ?>">- Éditeur: <?php echo htmlsecure($check[$name]) ?></span>
    </li>
  <?php endfor; ?>
  </ul>
  <?php if ( $action == $actions['view'] ): ?>
    <p class="submit"><input type="submit" name="checking" value="Valider" /></p>
  <?php elseif ( $action == $actions['edit'] ): ?>
    <p class="add"><a href="evt/infos/checklist.page.php?id=<?php echo $id ?>&edit" id="checklist-add">Ajouter</a></p>
  <?php endif ?>
<?php
  endif;
  $checklist->free();
}
?>
