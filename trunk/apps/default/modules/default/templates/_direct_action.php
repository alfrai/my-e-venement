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
<?php if ( $sf_user->hasCredential('tck-control') ): ?>
<div class="ui-widget ui-corner-all ui-widget-content li-direct-access">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h2><?php echo __('Direct access') ?></h2>
  </div>
  
  <form action="<?php echo cross_app_url_for('tck', 'ticket/control') ?>" method="get"><p>
    <input type="submit" name="go" value="<?php echo __('Ticket control') ?>" />
  </p></form>

</div>
<?php endif ?>
