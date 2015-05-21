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
  <?php
    $objects = array();
    if ( $object->hasRelation('Transactions') )
      $objects[] = $object;
    
    $config = $sf_data->getRaw('config');
    foreach ( $config['subobjects'] as $subobjects => $conf )
    foreach ( $object->$subobjects as $subobject )
    if ( $subobject->hasRelation('Transactions') )
      $objects[] = $subobject;
    $cpt = 0;
  ?>
  <ul class="pos">
    <?php foreach ( $objects as $obj ): ?>
    <?php $total = array('qty' => 0, 'value' => 0); ?>
    <?php $cpt++ ?>
    <?php if ( $obj->Transactions->count() > 0 ): ?>
    <li class="pos-<?php echo $cpt == 1 ? 'object' : 'subobject-'.$obj->id ?>">
      <?php if ( count($objects) > 1 ): ?>
      <h3><?php echo $obj ?></h3>
      <?php endif ?>
      <?php
        $bps = $sort = array();
        foreach ( $obj->Transactions as $transaction )
        if ( is_null($transaction->professional_id) || $cpt > 1 )
        foreach ( $transaction->BoughtProducts as $bp )
          require(__DIR__.'/side_widget_object_pos_process_products.php');
      ?>
      <ul>
        <li class="total">
          <span class="event">Total</span>:
          <span class="nb"><?php echo $total['qty'] ?></span>
          <?php if ( $sf_user->hasCredential('tck-ledger-sales') ): ?>
          <span class="value"><?php echo format_currency($total['value'],'€') ?></span>
          <?php endif ?>
        </li>
      </ul>
    </li>
    <?php endif ?>
    <?php endforeach ?>
    <?php if ( count($objects) == 0 || $total['qty'] == 0 ): ?>
    <li><?php echo __('No result',null,'sf_admin') ?></li>
    <?php endif ?>
  </ul>
