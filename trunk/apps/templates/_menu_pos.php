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
      if ( $sf_user->hasCredential('')
      ): ?>
      <li class="menu-event">
        <ul class="second">
          <?php if ( $sf_user->hasCredential('') ): ?>
          <li><a href="<?php echo cross_app_url_for('pos','product') ?>"><?php echo __('Products',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php include_partial('global/menu_extra', array('name' => 'pos')) ?>
       </ul>
        <span class="title"><?php echo __('Store',array(),'menu') ?></span>
      </li>
    <?php endif ?>
