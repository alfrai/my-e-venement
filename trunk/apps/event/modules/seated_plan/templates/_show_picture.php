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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php if ( isset($form) && !$form->getObject()->Picture->isNew()
        || isset($seated_plan) && is_object($sf_data->getRaw('seated_plan')->Picture) && !$seated_plan->Picture->isNew() ): ?>
<div class="sf_admin_form_row <?php if ( !isset($seated_plan) ): ?>sf_admin_boolean sf_admin_form_field_show_picture<?php endif ?>">
  <?php if ( isset($form) ): ?>
  <div class="tools ui-corner-all">
    <?php include_partial('show_picture_tools', array('seated_plan' => $form->getObject())) ?>
  </div>
  <?php endif ?>
  <div class="label ui-helper-clearfix"><label for="seated_plan_show_picture"><?php echo __('Picture').':' ?></label></div>
  <?php include_partial('global/magnify') ?>
  <?php include_partial('show_raw_picture',array('seated_plan' => $form->getObject())) ?>
</div>
<?php endif ?>
