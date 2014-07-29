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
          <li class="menu-setup-general"><a><?php echo __('General',array(),'menu') ?></a>
            <ul class="third">
              <li><?php echo cross_app_link_to(__('Change language', null, 'menu'), 'default', 'culture/index') ?></li>
              <?php if ( $sf_user->hasCredential('admin-users') ): ?>
              <li class="spaced"><a href="<?php echo cross_app_url_for('default','sfGuardUser') ?>"><?php echo __('Users',array(),'menu') ?></a></li>
              <?php endif ?>
              <?php if ( $sf_user->hasCredential('admin-power') ): ?>
              <li><a href="<?php echo cross_app_url_for('default','sfGuardGroup') ?>"><?php echo __('Groups',array(),'menu') ?></a></li>
              <li><a href="<?php echo cross_app_url_for('default','sfGuardPermission') ?>"><?php echo __('Permissions',array(),'menu') ?></a></li>
              <li class="spaced"><a href=""><?php echo __('Maintenance',array(),'menu') ?></a></li>
              <li><a href=""><?php echo __('Archiving',array(),'menu') ?></a></li>
              <li class="spaced"><a href="<?php echo cross_app_url_for('default','authentication') ?>"><?php echo __('System authentication logs',array(),'menu') ?></a></li>
              <?php endif ?>
              <?php if ( sfConfig::get('project_messaging_enable',false) && $sf_user->hasCredential('admin-power') ): ?>
              <li class="spaced"><a href="<?php echo cross_app_url_for('default','jabber') ?>"><?php echo __('Messaging','','menu') ?></a></li>
              <?php endif ?>
            </ul>
          </li>
