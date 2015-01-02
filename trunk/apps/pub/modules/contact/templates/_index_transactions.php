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
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php use_helper('Date') ?>
<div id="transactions">
<h2><?php echo __('Your commands') ?> :</h2>
<p class="help">(<?php echo __('You can review your previous orders by clicking on its number') ?>)</p>
<ul>
<?php foreach ( $contact->Transactions as $t ): ?>
<?php if ( $t->Order->count() > 0 && $t->Tickets->count() > 0 || $t->getPrice() > 0 ): ?>
  <li class="transaction-<?php echo $t->id ?>">
    #<a href="<?php echo url_for('transaction/show?id='.$t->id) ?>" class="transaction"><?php echo $t->id ?></a>
    <span class="date"><?php echo format_date($t->created_at) ?></span>
  </li>
<?php endif ?>
<?php endforeach ?>
</ul>
</div>
