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
<div class="sf_admin_list ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
  <table>
    <caption class="fg-toolbar ui-widget-header ui-corner-top">
      <h2><span class="ui-icon ui-icon-triangle-1-s"></span> <?php echo __("Email's Organisms List", array(), 'messages') ?></h2>
    </caption>
    <?php if (!$pager->getNbResults()): ?>
    <tbody>
      <tr class="sf_admin_row ui-widget-content">
        <td align="center" height="30">
          <p align="center"><?php echo __('No result', array(), 'sf_admin') ?></p>
        </td>
      </tr>
    </tbody>
    <?php else: ?>
    <thead class="ui-widget-header">
      <tr>
        <?php include_partial('list_th_tabular') ?>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th colspan="4">
          <div class="ui-state-default ui-th-column ui-corner-bottom">
            <?php include_partial('email_pagination', array('pager' => $pager, 'email_id' => $email_id)) ?>
          </div>
        </th>
      </tr>
    </tfoot>

    <tbody>
      <?php foreach ($pager->getResults() as $i => $organism): $odd = fmod(++$i, 2) ? ' odd' : '' ?>
      <tr class="sf_admin_row ui-widget-content <?php echo $odd ?>">
        <?php include_partial('list_td_tabular', array('organism' => $organism, 'professional' => NULL)) ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  <?php endif; ?>
  </table>

</div>
