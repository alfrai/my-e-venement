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
          <li><a><?php echo __('Events',array(),'menu') ?></a>
            <ul class="third">
              <li><a href=""><?php echo __('Meta-events',array(),'menu') ?></a></li>
              <li><a href="<?php echo cross_app_url_for('event','workspace') ?>"><?php echo __('Workspaces',array(),'menu') ?></a></li>
              <li class="spaced"></li>
              <li><a href=""><?php echo __('Event categories',array(),'menu') ?></a></li>
              <li><a href=""><?php echo __('Colors',array(),'menu') ?></a></li>
            </ul>
          </li>
