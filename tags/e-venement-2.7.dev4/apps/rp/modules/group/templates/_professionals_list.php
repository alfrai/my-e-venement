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
<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all members">
<div class="ui-widget-header ui-corner-all fg-toolbar"><h2><?php echo __("Group's professional members") ?></h2></div>
<ul class="professionals">
<?php foreach ( $group->Professionals as $professional ): ?>
<li>
  <strong><a class="file" href="<?php echo url_for('contact/edit?id='.$professional->Contact['id']) ?>"><?php echo $professional->Contact ?></a></strong>,
  <span class="professional"><?php echo $professional->getNameType() ?></span>
  <?php echo __('at') ?>
  <a href="<?php echo url_for('organism/show?id='.$professional->Organism['id']) ?>"><?php echo $professional->Organism ?></a>
</li>
<?php endforeach ?>
</ul>
<p class="nb"><?php echo $group->Professionals->count() ?> <?php echo __('element(s)') ?></p>
</div>
