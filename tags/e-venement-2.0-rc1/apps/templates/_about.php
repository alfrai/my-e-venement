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
<?php
  $firm = sfConfig::get('app_about_firm');
  $client = sfConfig::get('app_about_client');
  $translate = array(
    '%e%'       => '<strong>e-venement</strong>',
    '%client%'  => $client['name'],
    '%author%'  => 'Baptiste SIMON',
    '%firm%'    => 'Libre Informatique',
  );
?>
<div class="ui-widget ui-corner-all ui-widget-about ui-widget-content">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h2><?php echo __('About',null,'about') ?></h2>
  </div>
  <div class="contributors">
    <h3>e-venement</h3>
    <p><?php echo __("there is worst, but it's more expensive",null,'about') ?></p>
    <ul>
      <li><strong><?php echo __('the contributors',null,'about') ?></strong></li>
      <?php foreach ( sfConfig::get('app_about_contributors') as $contributor ): ?>
      <li><?php echo $contributor ?></li>
      <?php endforeach ?>
    </ul>
  </div>
  <div class="desc">
    <h3>e-venement</h3>
    <p class="version">version <?php echo sfConfig::get('app_about_version') ?></p>
    <p class="specific"><?php echo __('%e% for %client%',$translate,'about') ?></p>
    <p class="editor">
      <?php echo __('insurance',null,'about') ?>:
      <a href="<?php echo $firm['url'] ?>"><?php echo $firm['name'] ?></a>
      (<?php echo __('gov.',null,'about') ?>)
    </p>
  </div>
  <div class="mentions">
    <p class="copyleft">
      &copy; 2006-<?php echo date('Y') ?>
      <strong><a href="http://www.libre-informatique.fr/">libre informatique</a></strong>
      et
      <a href="#" class="show-contributors"><?php echo __('the contributors',null,'about') ?></a>.
      <?php echo __('All rights reserved',null,'about') ?>.
    </p>
    <p class="license">
      <?php echo __('%e% is licensed under the free license',$translate,'about') ?>
      <a href="http://www.gnu.org/licenses/gpl.html">GNU/GPL</a>.
      <?php echo __('This gives you freedoms, but also you must respect its clauses (attribution, share-alike)',null,'about') ?>.
    </p>
    <p class="tm">
      <?php echo __("%e% and the %e% logo are the properties of %author% and %firm%",$translate,'about') ?>.
      <?php echo __('All rights reserved',null,'about') ?>.
    </p>
    <form action="#" method="get" class="button-contribs">
      <button name="contribs" value="" class="show-contributors">
        <?php echo __('the contributors',null,'about') ?>
      </button>
    </form>
  </div>
</div>

