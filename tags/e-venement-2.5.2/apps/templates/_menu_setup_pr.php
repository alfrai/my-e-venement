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
          <li><a><?php echo __('P.R.',array(),'menu') ?></a>
            <ul class="third">
              <?php if ( $sf_user->hasCredential('admin-csv') ): ?>
              <li><a href="<?php echo cross_app_url_for('rp','option_csv') ?>"><?php echo __('Extractions',array(),'menu') ?></a></li>
              <?php endif ?>
              <?php if ( $sf_user->hasCredential('admin-labels') ): ?>
              <li><a href="<?php echo cross_app_url_for('rp','option_labels') ?>"><?php echo __('Labels',array(),'menu') ?></a></li>
              <?php endif ?>
              <?php if ( $sf_user->hasCredential('admin-titles') ): ?>
              <li class="spaced"><a href="<?php echo cross_app_url_for('rp','title_type') ?>"><?php echo __('Generic title',array(),'menu') ?></a></li>
              <?php endif ?>
              <?php if ( $sf_user->hasCredential('admin-phone') ): ?>
              <li><a href="<?php echo cross_app_url_for('rp','phone_type') ?>"><?php echo __('Types of phones',array(),'menu') ?></a></li>
              <?php endif ?>
              <?php if ( $sf_user->hasCredential('pr-contact-relationships-admin') ): ?>
              <li><a href="<?php echo cross_app_url_for('rp','contact_relationship_type') ?>"><?php echo __('Types of relationships',array(),'menu') ?></a></li>
              <?php endif ?>
              <?php if ( $sf_user->hasCredential('pr-social-admin') ): ?>
              <li class="spaced"><a href="<?php echo cross_app_url_for('rp','familial_quotient') ?>"><?php echo __('Familial quotients',array(),'menu') ?></a></li>
              <li><a href="<?php echo cross_app_url_for('rp','type_of_resources') ?>"><?php echo __('Types of resources',array(),'menu') ?></a></li>
              <li><a href="<?php echo cross_app_url_for('rp','familial_situation') ?>"><?php echo __('Familial situations',array(),'menu') ?></a></li>
              <?php endif ?>
              <?php if ( $sf_user->hasCredential('admin-pro') ): ?>
              <li class="spaced"><a href="<?php echo cross_app_url_for('rp','professional_type') ?>"><?php echo __('Types of functions',array(),'menu') ?></a></li>
              <?php endif ?>
              <?php if ( $sf_user->hasCredential('admin-org') ): ?>
              <li><a href="<?php echo cross_app_url_for('rp','organism_category') ?>"><?php echo __('Organism categories',array(),'menu') ?></a></li>
              <?php endif ?>
            </ul>
          </li>
