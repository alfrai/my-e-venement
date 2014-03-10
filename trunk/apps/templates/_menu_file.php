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
      <li class="menu-file">
        <ul class="second">
          <li><a href="" target="_blank"><?php echo __('New screen',array(),'menu') ?></a></li>
          <li><a href="<?php echo cross_app_url_for('default','default') ?>"><?php echo __('Index',array(),'menu') ?></a></li>
          <?php if ( $sf_user->isAuthenticated() && $url = sfConfig::get('project_archive_url',false) ): ?>
          <li><a target="_blank" href="<?php echo $url ?>"><?php echo __('Archives',array(),'menu') ?></a></li>
          <?php endif ?>
          <li class="spaced"><a href="#" onclick="javascript: window.sidebar.addPanel(document.title,window.location,'');"><?php echo __('Bookmark',array(),'menu') ?></a></li>
          <li><a href="#" onclick="javascript: if ( $('.sf_admin_action_print a').length > 0 ) $('.sf_admin_action_print a:first').click(); else print();"><?php echo __('Print',array(),'menu') ?></a></li>
          <li class="spaced"></li>
          <?php if ( $sf_user->isAuthenticated() ): ?>
          <li><a href="<?php echo cross_app_url_for('default','sf_guard_signout') ?>"><?php echo __('Logout',array(),'menu') ?></a></li>
          <?php endif ?>
          <li><a href="<?php echo cross_app_url_for('default','sf_guard_signout') ?>" onclick="javascript: window.close()"><?php echo __('Close',array(),'menu') ?></a></li>
        </ul>
        <span class="title"><?php echo __('Screen',array(),'menu') ?></span>
      </li>

