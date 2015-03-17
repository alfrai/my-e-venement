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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<div id="transactions">
<h2><?php echo __('Your commands') ?> :</h2>
<p class="help">(<?php echo __('You can review your previous orders by clicking on its number') ?>)</p>

<div id="sf_admin_container">
<div id="sf_admin_content">
<div class="sf_admin_list">
  <table cellspacing="0">
    <thead>
      <tr>
        <th class="sf_admin_text sf_admin_list_th_list_name"><?php echo __('Id') ?></th>
        <th class="sf_admin_date sf_admin_list_th_list_date"><?php echo __('Date') ?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th colspan="2"></th>
      </tr>
    </tfoot>
    <tbody>
      <?php $cpt = 0 ?>
      <?php foreach ( $contact->Transactions as $t ): ?>
      <?php if ( $t->Order->count() > 0 && $t->Tickets->count() > 0 || $t->getPrice() > 0 ): ?>
        <tr class="sf_admin_row <?php echo $cpt%2 == 0 ? '' : 'odd' ?> transaction-<?php echo $t->id ?>">
          <td class="sf_admin_text sf_admin_list_td_list_id">#<a href="<?php echo url_for('transaction/show?id='.$t->id) ?>" class="transaction"><?php echo $t->id ?></a></td>
          <td class="sf_admin_date sf_admin_list_td_list_date"><?php echo format_date($t->created_at) ?></td>
        </li>
        <?php $cpt++ ?>
      <?php endif ?>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
</div>
</div>
<?php use_helper('Date') ?>
<ul>
</ul>
</div>



