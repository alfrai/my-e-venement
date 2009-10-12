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
	$class .= " index";
  if ( $config['ticket']['new-bill'] ) includeJS('jquery');
  
  if ( ($transac = intval($_GET['unblock'])) > 0 && $user->evtlevel >= $config['evt']['right']['unblock'] )
  {
    $bd->updateRecordsSimple('transaction',array('id' => $transac),array('blocked' => 'f'));
    $nav->redirect($config["website"]["base"].'evt/bill/'.($_SESSION['ticket']['new-bill'] ? 'new-bill.php' : 'billing.php').'?t='.$transac);
  }
  
	includeLib("headers");
	
	if ( $config['ticket']['new-bill'] ):
?>
<script type="text/javascript">
  $(document).ready(function(){
    $('form').submit(function(){
      if ( $(this).find('input[name=new-bill]').remove().get(0).checked )
        $(this).attr('action','evt/bill/new-bill.php');
      return true;
    });
  });
</script>
<?php endif; ?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<h2>La billetterie</h2>
<form class="trans" action="evt/bill/billing.php" method="get">
	<p>
		<span>Reprise express d'une transaction classique (ne fonctionne pas pour dépôts et contingents)&nbsp;:</span>
		<span>#<input type="text" size="5" value="" name="t" id="focus" /></span>
		<?php if ( $config['ticket']['new-bill'] ): ?>
		<span><input type="checkbox" name="new-bill" value="yes" <?php if ($_SESSION['ticket']['new-bill'] !== false ) echo 'checked="checked"'; ?> title="Vous souhaitez utiliser la billetterie dans sa nouvelle forme ?" /></span>
		<?php endif; ?>
		<span class="hidden"><input type="submit" name="submit" value="" /></span>
		<?php if ( is_array($_SESSION["evt"]["express"]) ) { ?>
		|
		<span><input type="submit" name="unexpress" value="Normal" title="Revient sur une billetterie normale" /></span>
		<?php } ?>
	</p>
</form>
<?php if ( $user->evtlevel >= $config['evt']['right']['unblock'] ): ?>
<form class="trans unblock" action="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>" method="get">
  <p>
    Déverrouiller une opération :
    #<input type="text" name="unblock" value="<?php echo htmlsecure(intval($_GET['blocked']) > 0 ? intval($_GET['blocked']) : '') ?>" size="5" />
  </p>
</form>
<?php endif; ?>
<div class="desc"><?php @include("desc.txt"); ?></div>
<div class="highscore"><?php
	$query = " SELECT (SELECT count(*) FROM reservation_cur WHERE NOT canceled) AS selled,
		          (SELECT count(*) FROM reservation_pre AS pre, preselled AS sel WHERE pre.transaction = sel.transaction) AS preselled";
	$request = new bdRequest($bd,$query);
	echo "<span>".$request->getRecord("selled").' billets imprimés</span>';
	echo "<span>".$request->getRecord("preselled").' billets en attente</span>';
	$request->free();
?></div>
</div>
<?php
	includeLib("footer");
	$bd->free();
?>
