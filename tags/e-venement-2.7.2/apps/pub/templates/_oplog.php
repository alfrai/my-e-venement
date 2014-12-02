<?php
/**********************************************************************************
*
*     This file is part of e-venement.
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
<div id="oplog">
	<?php if ( !$sf_user->hasContact() ): ?>
	<?php echo link_to(__('No account yet? Create your own...'),'contact/new') ?>
  <?php echo link_to(__('Login'),'login/index') ?>
	<?php else: ?>
	<?php echo link_to(__('My account'),'contact/index') ?>
	<?php
		/**<div id="contact">
			<p class="name"><?php echo $sf_user->getContact() ?></p>
			<p class="address"><?php echo $sf_user->getContact()->address ?></p>
			<p>
				<span class="postalcode"><?php echo $sf_user->getContact()->postalcode ?></span>
				<span class="city"><?php echo $sf_user->getContact()->city ?></span>
			</p>
		</div>**/
  ?>
	<?php echo link_to(__('Logout'),'login/out') ?>
	<?php endif ?>
</div>
