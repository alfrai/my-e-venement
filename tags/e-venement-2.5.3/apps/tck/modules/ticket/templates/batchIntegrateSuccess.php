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
<?php include_partial('assets') ?>
<?php include_partial('global/flashes') ?>
<?php use_stylesheet('ticket-integrate') ?>
<div id="batch-integrate" class="ui-widget-content ui-corner-all">
<div class="fg-toolbar ui-widget-header ui-corner-all">
  <h1><?php echo __('Integrate tickets for %%manifestation%%',array('%%manifestation%%' => $manifestation)) ?></h1>
</div>
<div class="sf_admin_actions_block ui-widget">
  <?php include_partial('integrate_actions',array('manifestation' => $manifestation,)) ?>
</div>
<?php include_partial('batch_integrate_import',array(
  'form' => $importform,
  'manifestation' => $manifestation,
)) ?>
<?php include_partial('batch_integrate_pay',array(
  'form' => $payform,
  'manifestation' => $manifestation,
)) ?>
</div>
