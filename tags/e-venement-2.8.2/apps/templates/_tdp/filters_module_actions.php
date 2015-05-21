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
<?php if ( !isset($object) && $hasFilters ): ?>
<?php $savefilters = new FilterForm; ?>
<?php echo $savefilters->renderFormTag(cross_app_url_for('default', '@filter'), array('target' => '_blank', 'id' => 'sf_admin_filter_save')) ?><p>
        <?php echo $savefilters
          ->setAutoDefaults($sf_data->getRaw('sf_user'), $sf_data->getRaw('filters')->getModelName())
          ->setHidden()
          ->renderHiddenFields() ?>
      </p>
      <a href="<?php echo cross_app_url_for('default', 'filter/index?type='.$sf_data->getRaw('filters')->getModelName()) ?>" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only filters-list" target="_blank">
        <span class="ui-button-text"><?php echo __('List',null,'sf_admin') ?></span>
      </a>
      <button name="s" alt="<?php echo __("Filter's name", null, 'sf_admin') ?>" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only">
        <span class="ui-button-text"><?php echo __('Record',null,'sf_admin') ?></span>
      </button>
</p></form><?php use_javascript('filter-record') ?><?php use_stylesheet('filter-record') ?><?php endif ?>
