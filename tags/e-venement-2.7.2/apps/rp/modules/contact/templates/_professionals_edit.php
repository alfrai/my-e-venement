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
<script type="text/javascript">var professionals = []; var professional_new;</script>

<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all professional new">
  <div class="ui-widget-header ui-corner-all fg-toolbar"><h2><?php echo __('New professional') ?></h2></div>
  <div id="professional-new" class="sf_admin_form"></div>
  <script type="text/javascript">
    professional_new = '<?php echo url_for('professional/new') ?>';
    organism_ajax = '<?php echo url_for('organism/ajax') ?>';
    contact_ajax = '<?php echo url_for('contact/ajax') ?>';
  </script>
</div>

<?php foreach ( $contact->Professionals as $i => $professional ): ?>
<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all professional">
  <div class="ui-widget-header ui-corner-all fg-toolbar"><h2><?php echo __('Organism').': '.link_to($professional->Organism,'organism/edit?id='.$professional->organism_id) ?></h2></div>
  <div id="professional-<?php echo $i ?>" class="sf_admin_form"></div>
  <script type="text/javascript">professionals.push('<?php echo url_for('professional/edit?id='.$professional['id']) ?>');</script>
</div>
<?php endforeach ?>

