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
  <?php if ( $sf_user->hasCredential('pr-contact')
          || $sf_user->hasCredential('pr-organism')
          || $sf_user->hasCredential('pr-group')
          || $sf_user->hasCredential('pr-emailing')
          ): ?>
      <li class="menu-pr">
        <ul class="second">
          <?php if ( $sf_user->hasCredential('pr-contact') ): ?>
          <li><a href="<?php echo cross_app_url_for('rp','contact') ?>"><?php echo __('Contacts',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('pr-organism') ): ?>
          <li><a href="<?php echo cross_app_url_for('rp','organism') ?>"><?php echo __('Organisms',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('pr-group') ): ?>
          <li class="spaced"></li>
          <li><a href="<?php echo cross_app_url_for('rp','group') ?>"><?php echo __('Groups',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('pr-emailing') ): ?>
          <li><a href="<?php echo cross_app_url_for('rp','email') ?>"><?php echo __('Emailing',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('pr-card-view') ): ?>
          <li class="spaced"></li>
          <li><a href="<?php echo cross_app_url_for('rp','member_card/check') ?>"><?php echo __('Member card check',array(),'menu') ?></a></li>
          <li><a href="<?php echo cross_app_url_for('rp','member_card') ?>"><?php echo __('Member cards ledger',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php include_partial('global/menu_extra', array('name' => 'pr')) ?>
        </ul>
        <span class="title"><?php echo __('Pub. Rel.',array(),'menu') ?></span>
      </li>
  <?php endif ?>
