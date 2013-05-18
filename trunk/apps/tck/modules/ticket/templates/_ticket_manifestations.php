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
  <?php use_helper('Date','Number') ?>
  <?php if ( sfConfig::get('app_transaction_gauge_alert') ): ?>
  <div id="force-alert"><?php echo __("Warning: you've got full gauges !") ?></div>
  <div id="gauge-alert">dummy</div>
  <?php endif ?>
  <form action="<?php echo url_for('ticket/manifs?id='.($transaction instanceof Transaction ? $transaction->id : 0)) ?>" method="post">
    <a href="<?php echo url_for('ticket/gauge') ?>" id="gauge_url"></a>
    <div class="gauge ui-widget-content ui-corner-all"></div>
    <p class="manif_new">
      <input type="hidden" name="manifs-page" value="<?php echo isset($page) ? $page : 0 ?>" />
      <span class="title"><?php echo __('Manifestations') ?>:</span>
      <span><input type="text" name="manif_new" value="" /></span>
      <span><input type="checkbox" name="display_all" value="true" title="<?php echo __('Display even events hidden for ticketting') ?>" /></span>
      <a href="#" class="toggle_view"><?php echo __('hide / show') ?></a>
    </p>
    <ul class="manifestations_add ui-widget-content ui-corner-all">
    <?php foreach ( $manifestations_add as $manif ): ?>
      <li class="manif">
      <?php include_partial('ticket_manifestation',array(
        'manif' => $manif,
        'active' => false,
        'workspace' => isset($workspace) ? $workspace : true,
      )) ?>
      </li>
    <?php endforeach ?>
    </ul>
  </form>
