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
    <ul class="sf_admin_actions_form">
      <li class="sf_admin_action_list">
        <a class="fg-button ui-state-default fg-button-icon-left" href="<?php echo cross_app_url_for('event','manifestation/show?id='.$manifestation->id) ?>">
          <span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>
          <?php echo __('Return to manifestation') ?>
        </a>
      </li>
    </ul>
