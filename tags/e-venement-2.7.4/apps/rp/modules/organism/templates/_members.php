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
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  $helper = new organismGeneratorHelper();
?>
<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all members">
<div class="ui-widget-header ui-corner-all fg-toolbar"><h2><?php echo __("Organism's members") ?></h2></div>
<ul class="contacts">
<?php $action = !$sf_user->hasCredential('pr-contact-edit') ? 'show' : sfContext::getInstance()->getActionName(); ?>
<?php foreach ( $organism->Professionals as $professional ): ?>
<li>
  <a class="file" href="<?php echo url_for('contact/'.$action.'?id='.$professional->Contact['id']) ?>"><?php echo $professional->Contact ?></a>
  <span class="professional"><?php echo $professional ?> (<?php echo $professional->ProfessionalType ?>)</span>
  <span class="pictos"><?php echo $professional->getRaw('groups_picto') ?></span>
  <?php echo $helper->linkToDeletePro($professional, array(  'params' => 'class= fg-button ui-state-default fg-button-icon-left ',  'confirm' => 'Are you sure?',  'class_suffix' => 'delete',  'label' => 'Delete',  'ui-icon' => '',)) ?>
</li>
<?php endforeach ?>
</ul>
<p class="nb"><?php echo $organism->Professionals->count() ?> <?php echo __('element(s)') ?></p>
</div>
