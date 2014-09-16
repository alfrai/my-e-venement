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
<label><?php echo __('Groups') ?>:</label>
<ul class="show_groups">
  <?php foreach ( $contact->Groups as $group ): ?>
  <li>
    <?php echo $group->getRaw('html_tag') ?>
    <a href="<?php echo url_for('group/show?id='.$group->id) ?>"><?php echo $group ?></a>
  </li>
  <?php endforeach ?>
</ul>
<hr/>
<ul class="show_groups">
  <?php foreach ( $contact->Professionals as $pro ): ?>
  <?php if ( $pro->Groups->count() > 0 ): ?>
  <li>
    <?php echo $pro ?>
    <?php echo __('at') ?>
    <a href="<?php echo url_for('organism/edit?id='.$pro->Organism->id) ?>"><?php echo $pro->Organism ?></a>
    <ul>
      <?php foreach ( $pro->Groups as $group ): ?>
      <li>
        <span class="picture"><?php echo $group->getRaw('html_tag') ?></span>
        <a href="<?php echo url_for('group/show?id='.$group->id) ?>"><?php echo $group ?></a>
      </li>
      <?php endforeach ?>
    </ul>
  </li>
  <?php endif ?>
  <?php endforeach ?>
</ul>
