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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php if ( $sf_user->hasCredential('pr-card-admin') || $sf_user->hasCredential('tck-admin-member-cards') ): ?>
          <li><a><?php echo __('Member cards',array(),'menu') ?></a>
            <ul class="third">
              <?php if ( $sf_user->hasCredential('pr-card-admin') ): ?>
              <li><a href="<?php echo cross_app_url_for('rp','member_card_type') ?>"><?php echo __('Types',array(),'menu') ?></a></li>
              <?php endif ?>
              <?php if ( $sf_user->hasCredential('tck-admin-member-cards') ): ?>
              <li><a href="<?php echo cross_app_url_for('tck','@member_card_price_model') ?>"><?php echo __("Prices association",array(),'menu') ?></a></li>
              <?php endif ?>
            </ul>
          </li>
<?php endif ?>
