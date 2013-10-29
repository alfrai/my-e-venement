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
<?php $client = sfConfig::get('project_about_client', array()) ?>
<div class="ui-widget ui-corner-all ui-widget-customer ui-widget-content">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h2><?php echo __('Your organism') ?></h2>
  </div>

  <?php if ( isset($client['logo']) && $client['logo'] ): ?>
  <?php if ( !is_array($client['logo_attributes']) ) $client['logo_attributes'] = array(); ?>
  <p class="logo"><?php echo link_to(image_tag($client['logo'], array_merge($client['logo_attributes'], array('alt' => $client['name']))), $client['url'], array('target' => '_blank')) ?></p>
  <?php endif ?>

  <p class="name">
    <?php echo $client['name'] ?>
  </p>

  <?php if ( $client['address'] ): ?>
  <p class="address">
    <?php echo nl2br($client['address']) ?>
  </p>
  <?php endif ?>

  <p style="clear: both"></p>

</div>

