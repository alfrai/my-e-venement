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
    <?php if ( $sf_user->hasCredential('tck-reports')
            || $sf_user->hasCredential('tck-ledger-sales')
            || $sf_user->hasCredential('tck-ledger-cash') ): ?>
      <li class="menu-accounting">
        <ul class="second">
          <?php if ( $sf_user->hasCredential('tck-reports') ): ?>
          <?php if ( sfConfig::get('project_tickets_count_demands',false) ): ?>
          <li class="demands"><a href="<?php echo cross_app_url_for('tck','summary/asks') ?>"><?php echo __('Asks',array(),'menu') ?></a></li>
          <?php endif ?>
          <li class="orders"><a href="<?php echo cross_app_url_for('tck','order/index') ?>" class="order"><?php echo __('Orders',array(),'menu') ?></a></li>
          <li><a href="<?php echo cross_app_url_for('tck','invoice/index') ?>" class="invoice"><?php echo __('Invoices',array(),'menu') ?></a></li>
          <!--<li><a href="<?php echo cross_app_url_for('tck','summary/debts') ?>"><?php echo __('Debts',array(),'menu') ?></a></li>-->
          <li><a href="<?php echo cross_app_url_for('tck','summary/duplicatas') ?>"><?php echo __('Duplicatas',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('tck-reports') ): ?>
          <li class="spaced show-ticket"><a href="<?php echo cross_app_url_for('tck','ticket/show') ?>"><?php echo __("Ticket's log",array(),'menu') ?></a></li>
          <li class="debts-report"><a href="<?php echo cross_app_url_for('tck','debts/index') ?>"><?php echo __("Debts report",array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('tck-ledger-sales') ): ?>
          <li class="spaced"><a href="<?php echo cross_app_url_for('tck','ledger/sales') ?>"><?php echo __('Sales Ledger',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('tck-ledger-cash') ): ?>
          <li <?php if ( !$sf_user->hasCredential('tck-ledger-sales') ): ?>class="spaced"<?php endif ?>>
            <a href="<?php echo cross_app_url_for('tck','ledger/cash') ?>"><?php echo __('Cash Ledger',array(),'menu') ?></a>
          </li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('tck-ledger-cash') && $sf_user->hasCredential('tck-ledger-sales') ): ?>
          <li>
            <a href="<?php echo cross_app_url_for('tck','ledger/both') ?>"><?php echo __('Detailed Ledger',array(),'menu') ?></a>
          </li>
          <?php endif ?>
          <?php include_partial('global/menu_extra', array('name' => 'accounting')) ?>
        </ul>
        <span class="title"><?php echo __('Accounting',array(),'menu') ?></span>
      </li>
    <?php endif ?>
