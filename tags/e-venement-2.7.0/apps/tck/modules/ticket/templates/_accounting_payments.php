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
<?php if ( $transaction->Payments->count() > 0 ): ?>
<div id="payments" class="<?php echo isset($nocancel) && $nocancel ? 'nocancel' : '' ?>">
  <h2><?php echo __('Paid with', null, 'li_accounting') ?></h2>
  <div>
    <?php $value = 0 ?>
    <?php foreach ( $transaction->Payments as $payment ): ?>
    <?php $value += $payment->value ?>
    <p>
      <span class="method"><?php echo $payment->Method; ?></span>
      <span class="value"><?php echo format_currency($payment->value,'€') ?></span>
    </p>
    <?php endforeach ?>
    <?php if ( isset($nocancel) && $nocancel && $transaction->Translinked->count() > 0 ): ?>
    <?php foreach ( $transaction->Translinked as $trlinked ): ?>
    <?php foreach ( $trlinked->Payments as $payment ): ?>
    <?php $value += $payment->value ?>
    <p>
      <span class="method"><?php echo $payment->Method; ?></span>
      <span class="value"><?php echo format_currency($payment->value,'€') ?></span>
    </p>
    <?php endforeach ?>
    <?php endforeach ?>
    <?php endif ?>
    <p class="total">
      <span class="method"><?php echo __('Total', null, 'li_accounting') ?></span>
      <span class="value"><?php echo format_currency($value,'€') ?></span>
    </p>
    <p class="topay">
      <span class="method"><?php echo __('Still missing', null, 'li_accounting') ?></span>
      <span class="value"><?php echo format_currency($totals['tip'] <= $value ? 0 : $totals['tip'] - $value, '€') ?></span>
    </p>
  </div>
</div>
<?php endif ?>
