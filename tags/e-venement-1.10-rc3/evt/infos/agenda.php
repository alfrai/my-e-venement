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
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	require("conf.inc.php");
	includeClass("calendar");
	
	$css[] = "evt/styles/colors.css.php";
	$class = "agenda";
	
	if ( $config["evt"]["syndication"] )
	{
		$rss = array();
		$rss[] = array("title" => "e-venement, suivi des manifestations", "href" => $config["website"]["base"].'evt/infos/feed.php');
	}
	includeLib("headers");
	
	$month = intval($_GET["monthID"])."" == $_GET["monthID"] ? intval($_GET["monthID"]) : NULL;
	$year = intval($_GET["yearID"])."" == $_GET["yearID"] ? intval($_GET["yearID"]) : NULL;
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
	<h2>L'agenda</h2>
	<?php
		$year	= intval($_GET["yearID"]) > 0 ? intval($_GET["yearID"]) : date("Y");
		$month	= intval($_GET["monthID"]) > 0 ? intval($_GET["monthID"]) : date("m");
		
		$query	= " SELECT manifestation.id AS id, evenement.id AS evtid, manifestation.description,
			           manifestation.date, evenement.catdesc, evenement.nom, manifestation.duree,
			           evenement.code, site.nom AS sitenom, site.cp, site.ville AS site, colors.libelle AS colorname
			    FROM manifestation, evenement_categorie AS evenement, site, colors
			    WHERE manifestation.siteid = site.id
			      AND manifestation.evtid  = evenement.id
			      AND date >= '".$year."-".$month."-01'::date
			      AND date <  '".$year."-".$month."-01'::date + '1 month'::interval
			      AND ( manifestation.colorid = colors.id OR colors.id IS NULL AND manifestation.colorid IS NULL )
			    ORDER BY date, evenement.nom, site.pays, site.ville";
		$request = new bdRequest($bd,$query);
		
		$cal = new Calendar($year,$month);
		while ( $rec = $request->getRecordNext() )
		{
			$time		= strtotime($rec["date"]);
			$duree = explode(':',$rec['duree'] ? $rec['duree'] : '0:0:0');
			for ( $i = -1 ; strtotime('+ '.$duree[0].' hours '.$duree[1].' minutes '.$duree[2].' seconds',$time) > strtotime('23:59:59 + '.$i.' days',$time) ; $i++ )
			{
  			$tmp = strtotime('+'.($i+1).' days',$time);
	  		$date["year"]	= intval(date('Y',$tmp));
		  	$date["month"]	= intval(date('m',$tmp));
			  $date["day"]	= intval(date('d',$tmp));
			  $date["hour"]	= date('H',$tmp);
			  $date["minute"]	= date('i',$tmp);
			  
			  $content	 = '<span title="'.htmlsecure($rec['description'].' - '.($rec['typedesc'] ? $rec['typedesc'] : $rec['catdesc']).' - '.($rec["cp"] ? $rec["cp"].", " : "").$rec["site"]).'">';
  			if ( strtotime('+ '.$duree[0].' hours '.$duree[1].' minutes '.$duree[2].' seconds',$time) > strtotime('23:59:59 + '.($i+1).' days',$time) && $i < 0 )
  			  $content  .= '<span class="hour">'.htmlsecure($date["hour"].':'.$date["minute"].' ->').'</span> ';
  			elseif ( $i < 0 )
  			  $content  .= '<span class="hour">'.htmlsecure($date["hour"].':'.$date["minute"]).'</span> ';
  			elseif ( strtotime('+ '.$duree[0].' hours '.$duree[1].' minutes '.$duree[2].' seconds',$time) <= strtotime('23:59:59 + '.($i+1).' days',$time) && $i > 0 )
  			  $content  .= '<span class="hour">'.htmlsecure('-> '.date('H:i',strtotime('+ '.$duree[0].' hours '.$duree[1].' minutes '.$duree[0].' seconds',strtotime($rec["date"])))).'</span> ';
	  		$content	.= '<span class="evtsite">'.htmlsecure('('.$rec["sitenom"].')').'</span> ';
		  	$content	.= '<span class="evtnom">'.htmlsecure($rec["nom"]).'</span>';
			  $content  .= '</span>';
			  $cal->setEventContent($date["year"],$date["month"],$date["day"],$content,
				  		"evt/infos/manif.php?evtid=".intval($rec["evtid"])."&id=".intval($rec["id"])."&view",
					  	"eventcontent ".$rec["colorname"]);
			}
		}
		
		$request->free();
		
		echo $cal->showMonth();
	?>
</div>
<?php
	includeLib("footer");
?>
