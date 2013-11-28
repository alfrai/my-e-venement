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
<?php $phonenumbers = $form->getObject()->Phonenumbers ?>
<?php use_javascript('/sfFormExtraPlugin/js/jquery.autocompleter.js') ?>
<?php use_stylesheet('/sfFormExtraPlugin/css/jquery.autocompleter.css') ?>
<?php use_javascript('phonenumbers') ?>
<?php use_stylesheet('phonenumbers') ?>
<script type="text/javascript">
  var phonetype_ajax = '<?php echo url_for('phone_type/ajax') ?>';
  var phonenumbers = [];
  var pnid = '#<?php echo strtolower(get_class($form->getObject())) ?>_phonenumber_id';
</script>
<div class="sf_admin_form_row">
<!--<label><?php echo __('Phone numbers') ?></label>-->
<ul class="form_phonenumbers">
  <script type="text/javascript"><!--
    <?php foreach ( $phonenumbers as $number ): ?>
    phonenumbers.push('<?php echo url_for(($form->getObject() instanceof Organism ? 'organism' : 'contact').'_phonenumber/edit?id='.$number->id) ?>');
    <?php endforeach ?>
    phonenumbers.push('<?php echo url_for(($form->getObject() instanceof Organism ? 'organism' : 'contact').'_phonenumber/new') ?>');
  --></script>
</ul>
</div>
