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
    <?php include_partial('index') ?>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('default/list_footer') ?>
  </div>

  <?php include_partial('default/themeswitcher') ?>
</div>
