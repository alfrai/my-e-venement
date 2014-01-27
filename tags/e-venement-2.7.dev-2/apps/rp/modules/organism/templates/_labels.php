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
// those lines above come directly from e-venement v1.10 with only few modifications
?>
<?php $i = count($labels) ?>
<?php foreach ( $labels as $page ): ?>
<div class="page <?php $i--; echo $i == 0 ? 'last-child' : ''; ?>"><ul class="labels">
  <?php foreach ( $page as $line ): ?>
  <li>
    <?php foreach ( $line as $key => $cell ): ?>
    <div><div class="content">
    <p class="org"><span class="nom"><?php echo $cell['organism_name'] ?></span></p>
    <p class="adresse"><?php echo nl2br($cell['organism_address']) ?></p>
    <p class="ville"><span class="cp"><?php echo $cell['organism_postalcode'] ?></span> <span class="ville"><?php echo $cell['organism_city'] ?></span></p>
    <p class="pays"><?php echo $cell['organism_country'] ?></p>
    <p class="email"><?php echo $cell['organism_email'] ?></p>
    <p class="tel"><?php echo $cell['organism_phonenumber'] ?></p>
    </div></div>
    <?php if ( isset($line[$key+1]) ): ?>
      <div class="margin"></div>
    <?php endif ?>
    <?php endforeach; ?>
  </li>
  <?php endforeach; ?>
</ul></div>
<?php endforeach; ?>
