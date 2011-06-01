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
	includeClass("fpdf");
	
	global $pdf;
	
	// creation du PDF
	$pdf = new fpdf("P","mm",array(155,64));
	$pdf->SetAuthor('Baptiste SIMON - http://www.e-glop.net/');
	$pdf->SetCreator('e-venement, ticketting solution - http://www.e-glop.net/');
	$pdf->SetAutoPageBreak(false);
	$pdf->SetMargins(0,0,0);
	$pdf->AddFont('din');
	$pdf->AddFont('dinb');
	
	// $bill: infos à mettre sur le billet
	// $nb: nb de boucles pour les masstickets
	// $group: si true : billet groupé pour $bill["nbgroup"] personnes
	function getTicket(&$pdf, $bill, $nb = 1, $group = false )
	{
		global $config;
		if ( is_null($nb) ) $nb = 1;	// pour pouvoir passer NULL en 3è param.
		
		// boucle faite entre autre pour les masstickets
		for ( $i = 0 ; $i < $nb ; $i++ )
		{
		
		// relooking du contenu
		$tmp = array();
		$tmp["original"]	= "";
		$tmp["duplicata"]	= "duplicata";
		$tmp["annulation"]	= "annulation";
		$tmp["manifid"]		= "#".$bill["manifid"];
		$nbmax = $config["ticket"]["titlemaxchars"];
		$tmp["spectacle"]	= strlen($bill["evtnom"]) > $nbmax
					? substr($bill["evtnom"],0,$nbmax)."..."
					: $bill["evtnom"];
		$tmp["createurs"]	= implode(" / ",$bill["createurs"]);
		$tmp["organisateur"]	= "Très Tôt Théâtre";
		$buf = implode(" / ",$bill["orga"]);
		$nbmax = 50;
		if ( strlen($buf) > $nbmax ) $buf = substr($buf,0,$nbmax - 5)." ...";
		$tmp["organisateurs"]	= "Org: ".$buf." / ".$tmp["organisateur"];
		$tmp["placement"]	= intval($bill["plnum"]) > 0 ? "Place n°".intval($bill["plnum"]) : ("Placement Libre".($group ? ' - x'.$bill["nbgroup"] : ''));
		$tmp["billet"]		= date("d/m/Y H:i");
		$tmp["numop"]		= '#'.$bill["num"].'-'.$bill["operateur"];
		$tmp["conservation"]	= "À Conserver";
		$tmp["controle"]	= "Contrôle";
		$tmp["evenement"]	= $bill["metaevt"];
		$tmp["lieultl"]		= $bill["sitenom"];
		$tmp["lieubig"]		= $tmp["lieultl"];
		$tmp["prixltl"]		= round($bill["prix"],2)."E";
		$tmp["prixbig"]		= $bill["prix"] <= 0 ? "Exonéré" : (round($bill["prix"],2)." ".($bill["prix"] > 1 ? "euros" : "euro"));
		$tmp["depot"]		= $bill["depot"];
		
		// date et heure
		$time = strtotime($bill["date"]);
		$tmp["dateheureltl"]	 = date("d ",$time);
		$tmp["dateheureltl"]	.= strtolower($config["dates"]["moty"][intval(date("n",$time))-1]);
		$tmp["dateheureltl"]	.= date(" Y / H\hi",$time);
		$tmp["dateheurebig"]	 = strtolower($config["dates"]["DOTW"][date("w",$time)]).date(" d ",$time);
		$tmp["dateheurebig"]	.= strtolower($config["dates"]["MOTY"][intval(date("n",$time))-1]);
		$tmp["dateheurebig"]	.= date(" Y / H\hi",$time);
		
		foreach ( $tmp as $key => $value )
			$tmp[$key] = iconv("UTF-8","ISO-8859-15",$value);
		
		// la nouvelle page
		$pdf->addPage();
		//$pdf->Line(118,0,118,64);
		
		// partie de gauche
		$mr = 12; // marge de droite
		
		// mention original/duplicata/annulation
		$w   = 20;
		$h   = 5;
		$vpos = 6.5;
		$hpos = 118-$w-$mr+10-1;
		$pdf->SetXY($hpos,$vpos);
		$pdf->SetFont('din','',8);
		
		if ( isset($bill["duplicata"]) )
		{
			if ( $tmp["duplicata"] )
			$pdf->Cell($w,$h,$tmp["duplicata"],1,0,'C');
		}
		elseif ( isset($bill["annulation"]) )
		{
			if ( $tmp["annulation"] )
			{
				$pdf->SetTextColor(255);
				$pdf->Cell($w,$h,$tmp["annulation"],1,0,'C',1);
				$pdf->SetTextColor(0);
			}
		}
		else
		{
			if ( $tmp["original"] )
			$pdf->Cell($w,$h,$tmp["original"],1,0,'C');
		}
		
		// #id de la manifestation
		$wid = 20;
		$hpos = 7.5;
		$pdf->SetFont('dinb','',10);
		$pdf->SetXY($hpos,$vpos);
		$pdf->Cell($wid,$h,$tmp["manifid"]);
		
		// nom de l'organisme dépositaire
		$pdf->Cell(1.5);
		$nbmax = 45;
		$pdf->SetFont('din','',5);
		$pdf->Cell(118-20-1.5,$h,strlen($tmp["depot"]) > $nbmax ? substr($tmp["depot"],0,$nbmax-3)." ..." : $tmp["depot"]);
		
		$h = $vpos + 0.75;	// position Y du curseur
		
		// evenement
		$pdf->SetFont('dinb','',8);
		$pdf->SetXY(10,$h);
		$pdf->Cell(118-$mr,20,$tmp["evenement"],0,0,'R');
		
		// Date et heure de la séance
		$pdf->SetFont('dinb','',11);
		$pdf->SetXY(10,$h+=6);
		$pdf->Cell(118-$mr,20,$tmp["dateheurebig"],0,0,'R');
		
		// Lieu / prix
		$pdf->SetXY(10,$h+=5);
		$pdf->Cell(118-$mr,20,$tmp["lieubig"]." / ".$tmp["prixbig"],0,0,'R');
		
		// nom du spectacle
		$pdf->SetFont('dinb','',21);
		$pdf->SetXY(10,$h+=7);
		$pdf->Cell(118-$mr,20,$tmp["spectacle"],0,0,'R');
		
		// nom des createurs
		$pdf->SetFont('dinb','',12);
		$pdf->SetXY(10,$h+=7);
		$pdf->Cell(118-$mr,20,$tmp["createurs"],0,0,'R');
		
		// nom des organisateurs
		$pdf->SetFont('dinb','',8);
		$pdf->SetXY(10,$h+=7);
		$pdf->Cell(118-$mr,20,$tmp["organisateurs"],0,0,'R');
		
		// placement
		$pdf->SetFont('din','',8);
		$pdf->SetXY(10,$h+=5);
		$pdf->Cell(118-$mr,20,$tmp["placement"],0,0,'R');
		
		// infos billet
		$pdf->SetFont('din','',6);
		$pdf->SetXY(10,$h+=4.5);
		$pdf->Cell(118-$mr,20,$tmp["billet"].' / '.$tmp["numop"],0,0,'R');
		
		// à conserver
		$pdf->SetFont('din','',8);
		$pdf->SetXY(10,$h+=4);
		$pdf->Cell(118-$mr,20,$tmp["conservation"],0,0,'R');
		
		//////
		// partie de droite
		$ml = 118;	// marge de gauche
		$w   = 20;
		$h   = 5;
		$vpos = 6.5;
		$hpos = $ml+1.5;
		$pdf->SetXY($hpos,$vpos);
		$pdf->SetFont('din','',8);
		
		if ( isset($bill["duplicata"]) )
		{
			if ( $tmp["duplicata"] )
			$pdf->Cell($w,$h,$tmp["duplicata"],1,0,'C');
		}
		elseif ( isset($bill["annulation"]) )
		{
			if ( $tmp["annulation"] )
			{
				$pdf->SetTextColor(255);
				$pdf->Cell($w,$h,$tmp["annulation"],1,0,'C',1);
				$pdf->SetTextColor(0);
			}
		}
		else
		{
			if ( $tmp["original"] )
			$pdf->Cell($w,$h,$tmp["original"],1,0,'C');
		}
		
		// #id de la manifestation
		$wid = 20-3-1.5*2;	// 40mm de talon - 20mm de cadre "original" - 3mm de marge imprimante - 2*1.5mm de marges à gauche et au milieu
		$pdf->SetFont('dinb','',10);
		$pdf->Cell($wid,$h,$tmp["manifid"],0,0,'R');
		
		$h = $vpos + 0.75;		// position Y du curseur
		
		// evenement
		$pdf->SetFont('dinb','',7);
		$pdf->SetXY($ml,$h);
		$pdf->Cell($ml+3.5,20,$tmp["evenement"],0,0,'L');
		
		// Date et heure de la séance
		$pdf->SetFont('dinb','',8);
		$pdf->SetXY($ml,$h+=7.5);
		$pdf->Cell($ml+3.5,20,$tmp["dateheureltl"],0,0,'L');
		
		// Lieu / prix
		$pdf->SetXY($ml,$h+=4);
		$nbmax = 14;
		$pdf->Cell($ml+3.5,20,(strlen($tmp["lieultl"]) > $nbmax ? substr($tmp["lieultl"],0,$nbmax-3)." ..." : $tmp["lieultl"])." / ".$tmp["prixltl"],0,0,'L');
		
		// nom du spectacle
		$pdf->SetFont('dinb','',11);
		$pdf->SetXY($ml,$h+=7.5);
		$nbmax = 14;
		$pdf->Cell($ml+3.5,20,strlen($tmp["spectacle"]) > $nbmax ? substr($tmp["spectacle"],0,$nbmax-3)." ..." : $tmp["spectacle"],0,0,'L');
		
		// nom des createurs
		$pdf->SetFont('dinb','',8);
		$pdf->SetXY($ml,$h+=6);
		$nbmax = 20;
		$pdf->Cell($ml+3.5,20,strlen($tmp["createurs"]) > $nbmax ? substr($tmp["createurs"],0,$nbmax-3)." ..." : $tmp["createurs"],0,0,'L');
		
		// nom des organisateurs
		$pdf->SetFont('dinb','',8);
		$pdf->SetXY($ml,$h+=7);
		$pdf->Cell($ml+3.5,20,$tmp["organisateur"],0,0,'L');
		
		// placement
		$pdf->SetFont('din','',8);
		$pdf->SetXY($ml,$h+=5);
		$pdf->Cell($ml+3.5,20,$tmp["placement"],0,0,'L');
		
		// infos billet
		$pdf->SetFont('din','',6);
		$pdf->SetXY($ml,$h+=4.5);
		$pdf->Cell($ml+3.5,20,$tmp["billet"].' / '.$tmp["numop"],0,0,'L');
		
		// controle
		$pdf->SetFont('din','',8);
		$pdf->SetXY($ml,$h+=4);
		$pdf->Cell($ml+3.5,20,$tmp["controle"],0,0,'L');
		
		}
	}
?>
