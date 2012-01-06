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
<?php use_helper('I18N', 'Date', 'CrossAppLink') ?>
<?php include_partial('default/assets') ?>

<div class="about-home">
  <?php include_partial('global/about') ?>
</div>

<div id="sf_admin_container">
  <?php include_partial('default/flashes') ?>

  <div id="sf_admin_content">
  <div class="welcome ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
    <div class="ui-widget-content ui-corner-all">
      <div class="ui-widget-header ui-corner-all fg-toolbar">
        <h2><?php echo __('Welcome on e-venement', array(), 'messages') ?></h2>
      </div>
      <h3><?php echo __('Last updates') ?></h3>
      <ul>
        <?php if ( $sf_user->hasCredential('pr-emailing') ): ?>
        <li><?php echo __('Emails') ?>
          <ul>
            <?php foreach ($emails as $obj ): ?>
            <li>
              <?php echo format_date($obj->updated_at) ?>
              -
              <a href="<?php echo cross_app_url_for('rp','email/show?id='.$obj->id) ?>">
                <?php echo $obj->field_subject ?>
              </a>
              -
              <?php echo $obj->field_from ?>
            </li>
            <?php endforeach ?>
          </ul>
        </li>
        <?php endif ?>
        <?php if ( $sf_user->hasCredential('event-event') ): ?>
        <li><?php echo __('Manifestations') ?>
          <ul>
            <?php foreach ($manifestations as $obj ): ?>
            <li>
              <a href="<?php echo cross_app_url_for('event','manifestation/show?id='.$obj->id) ?>">
                <?php echo format_datetime($obj->happens_at) ?>
              </a>
              -
              <a href="<?php echo cross_app_url_for('event','event/show?id='.$obj->Event->id) ?>">
                <?php echo $obj->Event ?>
              </a>
            </li>
            <?php endforeach ?>
          </ul>
        </li>
        <?php endif ?>
        <?php if ( $sf_user->hasCredential('pr-contact') ): ?>
        <li><?php echo __('Contacts') ?>
          <ul>
            <?php foreach ($contacts as $obj ): ?>
            <li>
              <?php echo format_date($obj->updated_at) ?>
              -
              <a href="<?php echo cross_app_url_for('rp','contact/show?id='.$obj->id) ?>">
                <?php echo $obj ?>
              </a>
            </li>
            <?php endforeach ?>
          </ul>
        </li>
        <?php endif ?>
        <?php if ( $sf_user->hasCredential('pr-organism') ): ?>
        <li><?php echo __('Organisms') ?>
          <ul>
            <?php foreach ($organisms as $obj ): ?>
            <li>
              <?php echo format_date($obj->updated_at) ?>
              -
              <a href="<?php echo cross_app_url_for('rp','organism/show?id='.$obj->id) ?>">
                <?php echo $obj ?>
              </a>
            </li>
            <?php endforeach ?>
          </ul>
        </li>
        <?php endif ?>
      </ul>
    </div>

    <div class="ui-widget-content ui-corner-all">
      <div class="ui-widget-header ui-corner-all fg-toolbar">
        <h2><?php echo __('Libre Informatique', array(), 'messages') ?></h2>
      </div>
      <?php include_partial('global/libre-informatique') ?>
    </div>
  </div>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('default/list_footer') ?>
  </div>

  <?php include_partial('default/themeswitcher') ?>
</div>
