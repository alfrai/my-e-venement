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
<?php if ( $contact->MemberCards->count() > 0 ): ?>
<div id="member_cards">
<h2><?php echo sfConfig::get('app_member_cards_title',false) ? pubConfiguration::getText('app_member_cards_title') : __('Member card') ?></h2>
<h2><?php echo __('My products') ?> :</h2>

<div id="sf_admin_container">
<div id="sf_admin_content">
<div class="sf_admin_list">
  <table cellspacing="0">
    <thead>
      <tr>
        <th class="sf_admin_text sf_admin_list_th_list_name"><?php echo sfConfig::get('app_member_cards_title',false) ? pubConfiguration::getText('app_member_cards_title') : __('Member card') ?></th>
        <th class="sf_admin_text sf_admin_list_th_list_value"><?php echo __('Value') ?></th>
        <th class="sf_admin_text sf_admin_list_th_list_prices"><?php echo __('Associated prices still available') ?></th>
        <th class="sf_admin_date sf_admin_list_th_list_validity"><?php echo __('Validity') ?></th>
        <th class="sf_admin_date sf_admin_list_th_list_transaction_id"><?php echo __('Transaction') ?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th colspan="5"></th>
      </tr>
    </tfoot>
    <tbody>
      <?php $cpt = 0 ?>
      <?php foreach ( $products as $pdt ): ?>
      <tr class="sf_admin_row <?php echo $cpt%2 == 0 ? '' : 'odd' ?>">
        <td class="sf_admin_text sf_admin_list_td_list_name"><?php echo $pdt->name ?></td>
        <td class="sf_admin_text sf_admin_list_td_list_content"><?php
          echo $pdt->integrated_at && strtotime($pdt->integrated_at) <= time() && trim($pdt->getRawValue()->description_for_buyers)
            ? $pdt->getRawValue()->description_for_buyers
            : $pdt->declination
         ?></td>
        <td class="sf_admin_text sf_admin_list_td_list_transaction_id">#<?php echo link_to($pdt->transaction_id, 'transaction/show?id='.$pdt->transaction_id) ?></td>
        <td class="sf_admin_date sf_admin_list_td_list_date"><?php echo format_date($pdt->integrated_at) ?></td>
      </tr>
      <?php $cpt++ ?>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
</div>
</div>

<?php /*
<div id="member_cards">
<h2><?php echo __('Member cards') ?></h2>
<ul>
<?php foreach ( $contact->MemberCards as $mc ): ?>
  <li class="mc-<?php echo $mc->id ?>">
    <a href="<?php echo url_for('member_card/show?id='.$mc->id) ?>" class="mc"><?php echo $mc ?></a>
  </li>
<?php endforeach ?>
</ul>
<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#member_cards li a').each(function(){
      $.get($(this).attr('href'),function(data){
        data = $.parseHTML(data);
        mcid = $(data).find('#id').html();
        $('#member_cards .mc-'+mcid).html($(data).find('#sf_fieldset_none'));
      });
    });
  });
--></script>
</div>
*/ ?>
<?php endif ?>
