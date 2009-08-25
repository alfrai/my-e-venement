<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    beta-libs is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with beta-libs; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
  require("conf.inc.php");
  $css[] = 'evt/styles/jauge.css';
  $css[] = 'evt/styles/new-bill.css';
  $class .= " index";
  includeJS('jquery');
  includeJS('jquery-ui','evt');
  includeJS('new-bill','evt');
  
  includeLib("headers");
  
  $_SESSION['ticket']['new-bill'] = true;
  
  // respawning of an anciant transaction
  if ( ($transac = intval($_GET['t'])) > 0 )
  {
    $query =  ' SELECT transaction.*, pre.*, tarif.key
                FROM transaction, reservation_pre AS pre, tarif
                WHERE pre.transaction = transaction.id
                  AND tarif.id = tarifid
                  AND NOT pre.annul
                  AND pre.transaction = '.$transac;
    $request = new bdRequest($bd,$query);
?>
<script type="text/javascript">
  $(document).ready(function(){
  <?php
    // le client
    if ( $rec = $request->getRecord() )
    if ( $rec['personneid'] ):
      $client = intval($rec['fctorgid']) > 0 ? 'prof_'.$rec['fctorgid'] : 'pers_'.$rec['personneid'];
  ?>
    $('#bill-client .list').load('evt/bill/search-ppl.page.php?client=<?php echo $client ?> .list > ul',function(){
      $('#bill-client .list input[name=client]').get(0).checked = true;
      newbill_client_valid();
    });
  <?php endif; ?>
  <?php
    // les manifestations
    $manifs = array();
    while ( $rec = $request->getRecordNext() )
    if ( !in_array(intval($rec['manifid']),$manifs) )
      $manifs[] = intval($rec['manifid']);
  ?>
    $('#bill-tickets .list').load('evt/bill/search-evt.page.php?manifid[]=<?php echo implode('&manifid[]=',$manifs) ?> .list > ul',function(){
      $(this).find('input[name=manifs[]]').each(function(){
        $(this).get(0).checked = true;
        newbill_evt_select();
        newbill_evt_refreshjs();
      });
  <?php
    // les tickets
    $request->firstRecord();
  ?>
      $('#bill-tarifs input[type=text]').val(1);
  <?php  while ( $rec = $request->getRecordNext() ): ?>
      $('#bill-tickets .spectacles input[name=manifs[]][value=<?php echo $rec['manifid'] ?>]').get(0).checked = true;
      newbill_tickets_new_visu('<?php echo htmlspecialchars($rec['key']) ?>');
  <?php endwhile; ?>
      newbill_tickets_click_remove();
      newbill_tickets_refresh_money();
    });
    
    // les paiements
    <?php
      $query  = ' SELECT montant, modepaiementid, date::date
                  FROM paiement
                  WHERE transaction = '.$transac.'
                  ORDER BY date, sysdate';
      $request = new bdRequest($bd,$query);
    ?>
    <?php while ( $rec = $request->getRecordNext() ): ?>
    pay = $('#bill-paiement li').eq(0);
    pay.find('input.money').val(<?php echo floatval($rec['montant']) ?>);
    pay.find('select.mode').val(<?php echo intval($rec['modepaiementid']) ?>);
    pay.find('input.date').val('<?php echo date('Y-m-d',strtotime($rec['date'])) ?>').blur();
    newbill_paiement_print();
    <?php endwhile; ?>
    <?php
      $request->free();
    ?>
  });
</script>
<?php
    $request->free();
  }
  else
  {
    if ( !$bd->addRecord('transaction',array('accountid' => $user->getId(),)) )
    {
      $user->addAlert('Impossible de créer une opération en base, contactez votre administrateur');
      $nav->redirect('evt/bill/');
    }
    
    $transac = $bd->getLastSerial('transaction','id');
  }
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require(dirname(__FILE__).'/actions.php'); ?>
<div class="body">
<form action="evt/bill/new-bill-end.php" method="post" >
  <div id="bill-op">Opération #<span id="op"><?php echo $transac ?></span><input type="hidden" name="transac" value="<?php echo $transac ?>" /></div>
  <div id="bill-client">
    <p class="search">Client: <input type="text" name="search" value="" title="lancez la recherche, appuyez sur entrée" /> <a class="create" href="ann/fiche.php?new" target="_blank" title="Ouvre un nouvel onglet... fermez-le pour revenir.">Ajouter...</a></p>
    <div class="list"></div>
    <div class="microfiche"></div>
  </div>
  
  <div id="bill-tickets">
    <ul class="spectacles">
      <li class="total"><span>Total:</span> <span class="total">0</span></li>
    </ul>
    <p class="search">Spectacle: <input type="text" name="search" value="" title="lancez la recherche, appuyez sur entrée" /></p>
    <div class="list"></div>
    <div class="microfiche"></div>
  </div>
  
  <div id="bill-tarifs">
    <input type="text" name="nb" value="1" size="2" maxlength="3" />
    <button name="tarif" value="TP">TP</button>
    <button name="tarif" value="EXO">EXO</button>
    <button name="tarif" value="SCO">SCO</button>
    <button name="tarif" value="G">G</button>
    <span class="tickets" title="Cliquer pour retirer un billet"><span></span></span>
    <input class="ticket" type="hidden" name="" value="" />
  </div>
  
  <div id="bill-compta">
    <p>
      <button name="bdc" value="bdc" class="bdc">Bon de Commande</button> 
      <button name="facture" value="facture" class="facture">Facture</button> 
    </p>
    <p class="print">
      <button name="print" value="print" class="print">Imprimer les billets</button>
      <input type="checkbox" class="print" name="duplicata" value="1" title="Ré-imprimer des duplicatas, précisez le tarif :OA" />
      <input type="text" class="print" name="tarif" value="" title="Entrez le tarif que vous souhaitez dupliquer pour la manifestation sélectionnée" size="3" maxlength="6" />
      <?php if ( $config["ticket"]["enable_group"] ): ?>
      <span class="print group"><input type="checkbox" class="print group" name="group" value="1" title="Billets groupés ?" /></span>
      <?php endif; ?>
    </p>
  </div>
  
  <div id="bill-verify"><input type="submit" name="verify" value="vérifier et valider"/></div>
  <div id="bill-paiement">
    <button id="pay" name="letsgo" value="">Payer</button>
    <p class="total">À payer&nbsp;: <span></span>€</p>
    <ul>
      <li>
        <p><span>montant&nbsp;:</span> <span><input class="money" type="text" name="reglement[money][]" value="" /> €</span></p>
        <?php $request = new bdRequest($bd,' SELECT * FROM modepaiement ORDER BY libelle'); ?>
        <p><span>mode de règlement&nbsp;:</span> <span><select name="reglement[mode][]" class="mode">
          <option value="">-mode de règlement-</option>
          <?php while ( $rec = $request->getRecordNext() ): ?>
            <option value="<?php echo $rec['id'] ?>"><?php echo htmlspecialchars($rec['libelle']) ?></option>
          <?php endwhile; ?>
        </select></span></p>
        <?php $request->free(); ?>
        <p><span>date&nbsp;:</span> <span><input class="date" type="text" name="reglement[date][]" value="" /></span></p>
        <p class="valid"><span><input type="submit" name="reglement[valider]" value="ajouter" /></span></p>
      </li>
    </ul>
  </div>
</form>

<div id="warning">MESSAGE D'ALERTE</div>

</div>
<?php
	includeLib("footer");
	$bd->free();
?>
