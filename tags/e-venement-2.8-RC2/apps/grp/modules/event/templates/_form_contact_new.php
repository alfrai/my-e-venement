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
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
            <?php echo $f->renderHiddenFields(); ?>
            <p title="<?php echo __('Contact') ?>"><?php echo $f['professional_id'] ?></p>
            <p title="<?php echo __('Note') ?>"><?php echo $f['comment1'] ?></p>
            <p title="<?php echo __('Confirmation') ?>"><?php echo $f['comment2'] ?></p>
            <p title="<?php echo __('Confirmed') ?>"><?php echo $f['confirmed'] ?></p>
            <p class="sf_admin_actions">
              <input type="submit" value="<?php echo __('Save',array(),'sf_admin') ?>" />
            </p>
