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
<?php include_partial('global/flashes') ?>
<div id="contact">
  <h1><?php echo $contact ?></h1>
  <p><a href="<?php echo url_for('contact/edit') ?>">modifier</a></p>
</div>

<?php if ( $manifestations->count() > 0 ): ?>
<div id="manifestations">
<h2><?php echo __('Manifestations') ?></h2>
<ul>
<?php foreach ( $manifestations  as $manif ): ?>
  <li>
    <span class="manif"><?php echo $manif ?></span>
    <span class="transaction_id"><?php $arr = array(); foreach ( $manif->Tickets AS $tck ) $arr[] = $tck->Transaction; echo '#'.implode(', #',$arr); ?></span>
  </li>
<?php endforeach ?>
</ul>
</div>
<?php endif ?>

<?php if ( $contact->MemberCards->count() > 0 ): ?>
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
        mcid = $(data).find('#id').html();
        $('#member_cards .mc-'+mcid).html($(data).find('#sf_fieldset_none'));
      });
    });
  });
--></script>
</div>
<?php endif ?>
