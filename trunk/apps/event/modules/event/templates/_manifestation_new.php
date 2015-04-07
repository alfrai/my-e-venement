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
<?php if ( !$form->isNew() ): ?>
<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h2 class="new_manifestation"><a title="<?php echo __('Records your event before it opens a new manifestation screen') ?>" id="manifestation-new" href="<?php echo url_for('manifestation/new?event='.$event->slug) ?>"><?php echo __('New manifestation') ?></a></h2>
    <h2 class="import_ics"><a title="<?php echo __('Records your event before it opens a new manifestation screen') ?>" id="manifestations-import-ics" href="<?php echo url_for('event/import?id='.$event->id) ?>" class="fg-button ui-state-default fg-button-icon-left">
        <span class="ui-icon ui-icon-calendar"></span>
        <?php echo __('Import an ICS file') ?>
    </a></h2>
  </div>
</div>
<?php endif ?>
