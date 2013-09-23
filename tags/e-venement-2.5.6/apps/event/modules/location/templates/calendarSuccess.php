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
<?php use_javascript('jquery','first') ?>
<?php use_stylesheet('/sfAdminThemejRollerPlugin/css/jquery/redmond/jquery-ui.custom.css') ?>
<?php use_stylesheet('/sfAdminThemejRollerPlugin/css/jroller.css') ?>
<?php use_stylesheet('/sfAdminThemejRollerPlugin/css/fg.menu.css') ?>
<?php use_stylesheet('/sfAdminThemejRollerPlugin/css/fg.buttons.css') ?>
<?php use_stylesheet('/sfAdminThemejRollerPlugin/css/ui.selectmenu.css') ?>
<div id="more" class="ui-widget-content ui-widget sf_admin_edit">
<?php include_partial('manifestation_calendar', array('location' => $location, 'form' => $form, 'configuration' => $configuration)) ?>
</div>
