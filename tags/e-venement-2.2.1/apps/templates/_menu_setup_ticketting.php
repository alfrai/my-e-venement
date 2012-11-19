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
          <li><a><?php echo __('Ticketting',array(),'menu') ?></a>
            <ul class="third">
              <?php if ( $sf_user->hasCredential('tck-admin-payment') ): ?>
              <li><a href="<?php echo cross_app_url_for('tck','@payment_method') ?>"><?php echo __('Payment methods',array(),'menu') ?></a></li>
              <?php endif ?>
              <?php if ( $sf_user->hasCredential('event-admin-price') ): ?>
              <li><a href="<?php echo cross_app_url_for('event','price') ?>"><?php echo __('Prices',array(),'menu') ?></a></li>
              <?php endif ?>
              <?php if ( $sf_user->hasCredential('tck-transaction') ): ?>
              <li><a href="<?php echo cross_app_url_for('event','@workspace_user_ordering') ?>"><?php echo __('Ordering your workspaces',array(),'menu') ?></a></li>
              <?php endif ?>
              <?php if ( $sf_user->hasCredential('tck-admin-member-cards') ): ?>
              <li><a href="<?php echo cross_app_url_for('tck','@member_card_price_model') ?>"><?php echo __("Prices association for member cards",array(),'menu') ?></a></li>
              <?php endif ?>
            </ul>
          </li>
