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
      <?php $view = false ?>
      <li class="menu-setup">
        <ul class="second">
          <?php if ( $sf_user->hasCredential('admin-power') || $sf_user->hasCredential('admin-users') ): ?>
            <?php $view = true ?>
            <?php include_partial('global/menu_setup_general') ?>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('admin-csv')
                  || $sf_user->hasCredential('admin-labels')
                  || $sf_user->hasCredential('admin-titles')
                  || $sf_user->hasCredential('admin-phone')
                  || $sf_user->hasCredential('admin-pro')
                  || $sf_user->hasCredential('admin-org')
                ): ?>
            <?php $view = true ?>
            <?php include_partial('global/menu_setup_pr') ?>
          <?php endif ?>
          <?php include_partial('global/menu_setup_events') ?>
          <?php if ( $sf_user->hasCredential('tck-admin-payment')
                  || $sf_user->hasCredential('event-admin-price')
                  || $sf_user->hasCredential('tck-transaction') ): ?>
          <?php include_partial('global/menu_setup_ticketting') ?>
          <?php endif ?>
          <?php include_partial('global/menu_setup_groups') ?>
          <?php include_partial('global/menu_setup_mc') ?>
          <?php include_partial('global/menu_setup_online') ?>
        </ul>
        <?php if ( $view ): ?>
        <span class="title"><?php echo __('Settings',array(),'menu') ?></span>
        <?php endif ?>
      </li>
