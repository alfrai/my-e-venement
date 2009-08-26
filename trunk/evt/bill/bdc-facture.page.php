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
*    Copyright (c) 2006-2009 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
    global $css, $compta, $config, $data;
    
    // à savoir s'il s'agit d'une facture ou d'un BdC
    $type = intval(substr($compta[0][0],0,1)) > 0 ? 'bdc' : 'facture';
    
    // si on sort le BdC en html
    $title = 'BdC';
    $css[] = 'evt/styles/bdc-facture.css';
    includeLib('headers');
?>
    <script type="text/javascript" language="javascript">
      function load()
      {
        print();
        <?php if ( !$config['ticket']['let_open_after_print'] ): ?>
        window.location = "evt/bill/billing.php?<?php echo 't='.$data['numtransac'] ?><?php echo $type == 'bdc' ? '' : '&s=4' ?>";
        <?php endif; ?>
      }
    </script>
    
<?php
    echo '<div id="seller">';
    $seller = $config['ticket']['seller'];
    if ( is_array($seller) )
    {
      if ( $seller['logo'] )
      echo '<p class="logo"><img alt="logo" src="'.htmlsecure($seller['logo']).'" /></p>';
      unset($seller['logo']);
      
      foreach ( $seller as $key => $value )
        echo '<p class="'.htmlsecure($key).'">'.htmlsecure($value).'</p>';
    }
    echo '</div>';
    
    // les données client
    $tmp = array_shift($compta);
    $customer = array('bdcid','prenom','nom','orgnom','adresse','cp','ville','pays','transaction');
    echo '<div id="customer">';
    foreach ( $customer as $key => $value )
      echo '<p class="'.$value.'">'.$tmp[$key].'</p>';
    echo '</div>';
    
    // récupération du numéro de bon de commande et de transaction
    $id = $tmp[0];
    $transac = $tmp[count($tmp)-1];
    
    echo '<p id="ids">'.($type == 'bdc' ? 'Bon de commande #' : 'Facture ' ).'<span class="id">'.htmlsecure($id).'</span> (pour l\'opération <span 
    class="transac">#'.$transac.'</span>)</p>';
    
    // les lignes du bdc
    $ligne = array('evt','date','heure','salle','ville','cp','tarif','nb','pu','ttc','tva','ht');
    $totaux = array('ht' => 0, 'tva' => array(), 'ttc' => 0);
    $engil = array(); // permet d'avoir le rang d'une valeur recherché
    foreach ( $ligne as $key => $value )
      $engil[$value] = $key;
    
    echo '<table id="lines">';
    while ( $tmp = array_shift($compta) )
    {
      $tva = floatval(str_replace(',','.',$tmp[$engil['tva']]))/100;
      
      // les totaux
      $totaux['ttc'] += $tmp[$engil['ttc']];
      $totaux['ht'] += $tmp[$engil['ttc']]/(1+$tva); 
      $totaux['tva'][$tmp[$engil['tva']].''] += $tmp[$engil['ttc']] - $tmp[$engil['ttc']]/(1+$tva);
      
      // les arrondis, les calculs TVA
      $tmp[$engil['ht']]    = round($tmp[$engil['ttc']]/(1+$tva),2);
      $tmp[$engil['pu']]    = round($tmp[$engil['pu']],2);
      $tmp[$engil['ttc']]   = round($tmp[$engil['ttc']],2);
      
      $tmp[$engil['date']]  = date('d/m/Y',strtotime($tmp[$engil['date']]));
      echo '<tr>';
      foreach ( $ligne as $key => $value )
        echo '<td class="'.$value.'">'.$tmp[$key].'</td>';
      echo '</tr>';
    }
    echo '
      <thead><tr>
        <th class="evt">Evènement</th>
        <th class="date">Date</th>
        <th class="heure">Heure</th>
        <th class="salle">Salle</th>
        <th class="ville">Ville</th>
        <th class="cp">CP</th>
        <th class="tarif">Tarif</th>
        <th class="nb">Qté</th>
        <th class="pu">PU TTC</th>
        <th class="ttc">TTC</th>
        <th class="tva">TVA</th>
        <th class="ht">HT</th>
      </tr></thead>';
    echo '</table>';
    
    echo '<div id="totaux">';
      echo '<p class="total"><span>Total HT:</span><span class="float">'.round($totaux['ht'],2).'</span></p>';
      foreach ( $totaux['tva'] as $key => $value )
      echo '<p class="tva"><span>TVA '.$key.'%:</span><span class="float">'.round($value,2).'</span></p>';
      echo '<p class="ttc"><span>Total TTC:</span><span class="float">'.round($totaux['ttc'],2).'</span></p>';
    echo '</div>';
    
    includeLib('footer');
?>
