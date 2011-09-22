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
      <li>
        <ul class="second">
          <?php if ( $sf_user->hasCredential('tck-transaction') ): ?>
          <li><a href="<?php echo cross_app_url_for('tck','ticket/sell') ?>"><?php echo __('New transaction',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('tck-unblock') ): ?>
          <li><a href="<?php echo cross_app_url_for('tck','ticket/respawn') ?>"><?php echo __('Respawn a transaction',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('tck-control') ): ?>
          <li><a href="<?php echo cross_app_url_for('tck','ticket/control') ?>"><?php echo __('Ticket control',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('tck-cancel') && $sf_user->hasCredential('tck-transaction') ): ?>
          <li class="spaced"><a href="<?php echo cross_app_url_for('tck','ticket/cancel') ?>"><?php echo __('Cancelations',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php if ( $sf_user->hasCredential('tck-print-ticket') && $sf_user->hasCredential('tck-transaction') ): ?>
          <li><a href="<?php echo cross_app_url_for('tck','ticket/duplicate') ?>"><?php echo __('Duplicate tickets',array(),'menu') ?></a></li>
          <?php endif ?>
          <!--
          <li class="spaced"><a href="<?php echo cross_app_url_for('tck','deposit/send') ?>"><?php echo __('Deposit',array(),'menu') ?></a></li>
          <li><a href="<?php echo cross_app_url_for('tck','deposit/getBack') ?>"><?php echo __('Sells',array(),'menu') ?></a></li>
          <li><a href="<?php echo cross_app_url_for('tck','deposit/inProgress') ?>"><?php echo __('In progress',array(),'menu') ?></a></li>
          -->
          <?php if ( $sf_user->hasCredential('tck-reports') ): ?>
          <li class="spaced demands"><a href="<?php echo cross_app_url_for('tck','summary/asks') ?>"><?php echo __('Asks',array(),'menu') ?></a></li>
          <li class="orders"><a href="<?php echo cross_app_url_for('tck','order/index') ?>" class="order"><?php echo __('Orders',array(),'menu') ?></a></li>
          <li><a href="<?php echo cross_app_url_for('tck','invoice/index') ?>" class="invoice"><?php echo __('Invoices',array(),'menu') ?></a></li>
          <li><a href="<?php echo cross_app_url_for('tck','summary/debts') ?>"><?php echo __('Debts',array(),'menu') ?></a></li>
          <li><a href="<?php echo cross_app_url_for('tck','summary/duplicatas') ?>"><?php echo __('Duplicatas',array(),'menu') ?></a></li>
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
        </ul>
        <span class="title"><?php echo __('Ticketting',array(),'menu') ?></span>
      </li>
