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
<div class="ui-widget ui-corner-all ui-widget-search ui-widget-content">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h2><?php echo __('Global Search') ?></h2>
  </div>
  
  <form action="<?php echo cross_app_url_for('default', 'search/index') ?>" method="get"><p>
    <input type="text" name="search" value="" size="18" />
    <input type="submit" name="go" value="<?php echo __('Search') ?>" />
  </p></form>

</div>

