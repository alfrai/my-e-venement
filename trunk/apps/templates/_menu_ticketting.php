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
    <?php if ( $sf_user->hasCredential('tck-transaction')
            || $sf_user->hasCredential('tck-unblock')
            || $sf_user->hasCredential('tck-control')
            || $sf_user->hasCredential('tck-cancel')
            || $sf_user->hasCredential('tck-print-ticket') ): ?>
      <li>
        <ul class="second">
          <?php if ( $sf_user->hasCredential('tck-transaction') ): ?>
          <li><a href="<?php echo cross_app_url_for('tck','ticket/sell') ?>"><?php echo __('New transaction',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('tck-unblock') ): ?>
          <li><a href="<?php echo cross_app_url_for('tck','ticket/respawn') ?>"><?php echo __('Respawn a transaction',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('tck-transaction') && $sf_user->hasCredential('tck-transaction-touchy') ): ?>
          <li class="spaced"><a href="<?php echo cross_app_url_for('tck','transaction/new') ?>"><?php echo __('New transaction (touchscreens)',array(),'menu') ?></a></li>
          <?php if ( $sf_user->hasCredential('tck-unblock') ): ?>
          <li><a href="<?php echo cross_app_url_for('tck','transaction/respawn') ?>"><?php echo __('Respawn a transaction',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php endif ?>
          
          <?php if ( $sf_user->hasCredential('tck-cancel') && $sf_user->hasCredential('tck-transaction') ): ?>
          <li class="spaced"><a href="<?php echo cross_app_url_for('tck','ticket/cancel') ?>"><?php echo __('Cancelling tickets',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('tck-print-ticket') && $sf_user->hasCredential('tck-transaction') ): ?>
          <li><a href="<?php echo cross_app_url_for('tck','ticket/duplicate') ?>"><?php echo __('Duplicate tickets',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( (sfConfig::has('app_control_future') || sfConfig::has('app_control_past') || sfConfig::has('app_control_delays'))
                  && $sf_user->hasCredential('tck-control-overview') ): ?>
          <li><a href="<?php echo cross_app_url_for('tck','control/index') ?>"><?php echo __('Affichage des flux',array(),'menu') ?></a></li>
          <?php endif ?>
          
          <?php if ( $sf_user->hasCredential('tck-control') ): ?>
          <li class="spaced"><a href="<?php echo cross_app_url_for('tck','ticket/control') ?>"><?php echo __('Ticket control',array(),'menu') ?></a></li>
          <?php endif ?>
        </ul>
        <span class="title"><?php echo __('Ticketting',array(),'menu') ?></span>
      </li>
    <?php endif ?>
