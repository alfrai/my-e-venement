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
<?php if ( $sf_user->hasCredential('stats-attendance')
        || $sf_user->hasCredential('stats-activity')
        || $sf_user->hasCredential('stats-prices')
        || $sf_user->hasCredential('stats-byGroup') ): ?>
      <li>
        <ul class="second">
          <?php if ( $sf_user->hasCredential('stats-attendance') ): ?>
          <li><a href="<?php echo cross_app_url_for('stats','attendance/index') ?>"><?php echo __('Attendance',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('stats-prices') ): ?>
          <li><a href="<?php echo cross_app_url_for('stats','prices/index') ?>"><?php echo __('Tickets by price',array(),'menu') ?></a></li>
          <li><a href="<?php echo cross_app_url_for('stats','prices/index') ?>"><?php echo __('Transactions by price',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('stats-activity') ): ?>
          <li><a href="<?php echo cross_app_url_for('stats','activity/index') ?>"><?php echo __('Ticketting activity',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('stats-byGroup') ): ?>
          <li class="spaced"><a href="<?php echo cross_app_url_for('stats','byGroup/index') ?>"><?php echo __('Entrances by group',array(),'menu') ?></a></li>
          <?php endif ?>
        </ul>
        <span class="title"><?php echo __('Stats',array(),'menu') ?></span>
      </li>
<?php endif ?>
