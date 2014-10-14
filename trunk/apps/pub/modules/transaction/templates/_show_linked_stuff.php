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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<ul>
  <?php $collection = array('LinkedProducts' => array()); ?>
  <?php foreach ( array($ticket->Manifestation, $ticket->Price, $ticket->Gauge->Workspace, $ticket->Manifestation->Event->MetaEvent) as $object ): ?>
  <?php if ( $object->getTable()->hasRelation($rel = 'LinkedProducts') ): ?>
    <?php $links = $object->$rel->getData()->getRawValue(); ?>
    <?php foreach ( $links as $link ): ?>
    <?php if ( in_array($link->id, $collection[$rel]) ) continue ?>
    <?php $collection[$rel][] = $link->id ?>
    <?php if (!( $link instanceof liUserAccessInterface && !$link->isAccessibleBy($sf_user->getRawValue()) )): ?>
    <?php $max_price = $link->getMostExpansivePrice($sf_user->getRawValue()) ?>
    <?php if ( $max_price['price'] ): ?>
      <li><form method="get" action="<?php echo url_for('store/modForTicket') ?>">
        <input type="hidden" name="link[ticket_id]" value="<?php echo $ticket->id ?>" />
        <input type="hidden" name="link[product_id]" value="<?php echo $link->id ?>" />
        <?php $did = array(); foreach ( $ticket->BoughtProducts as $bp ) $did[] = $bp->product_declination_id; ?>
        <span class="product"><?php echo $link ?></span>
        <span class="price"><?php echo format_currency($max_price['value'],'€') ?></span>
        <br/>
        <select class="declination" name="link[declination_id]" onchange="javascript: $(this).closest('form').submit();">
          <option></option>
          <?php foreach ( $link->Declinations as $declination ): ?>
          <option
            <?php if ( in_array($declination->id, $did) ): ?>selected="selected"<?php endif ?>
            value="<?php echo $declination->id ?>"
          >
            <?php echo $declination ?>
          </option>
          <?php endforeach ?>
        </select>
      </form></li>
    <?php endif ?>
    <?php endif ?>
    <?php endforeach ?>
  <?php endif ?>
  <?php endforeach ?>
</li>
