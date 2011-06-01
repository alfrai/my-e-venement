/**********************************************************************************
*
*	    This file is part of beta-libs.
*
*    beta-libs is free software; you can redistribute it and/or modify
*    it under the terms of the GNU Lesser General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    beta-libs is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Lesser General Public License for more details.
*
*    You should have received a copy of the GNU Lesser General Public License
*    along with beta-libs; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
	function _closeBill(elt,classname)
	{
		elt.className = resumeClass+" "+classname;
	}
	
	function printBill(button,manifid,resa,key,transac)
	{
		if ( resa && manifid && transac )
		{
			url = "evt/bill/ticket.hide.php?manifid="+manifid+"&resa="+resa+"&transac="+transac;
			if ( document.formu.group && document.formu.group.checked )
				url += "&group";
			for ( i = 0 ; input = button.parentNode.getElementsByTagName("input").item(i) ; i++ )
			if ( input.type == "text" )
				url += "&plnum[]="+input.value;
			window.open(url,"_ticket","");
			
			var elt = document.getElementById("billets"+manifid+"."+key);
			_closeBill(elt,"done");
			elt = document.getElementById("demat");
			if ( elt )
				elt.parentNode.parentNode.removeChild(elt.parentNode);
			
			// passage au suivant
			e = document.getElementById('next');
			i = document.getElementById('sure');
			if ( e ) e.disabled = false;
			if ( i ) i.checked = true;
		}
		else alert("Impossible d'imprimer le(s) billet(s) -manque d'informations-");
	}
	
	// mm chose que printBill() mais pour les tickets "en dépôt"
	function printDepot(button,manifid,resa,key,transac)
	{
		if ( resa && manifid && transac )
		{
			url = "evt/bill/massticket.hide.php?manifid="+manifid+"&resa="+resa+"&transac="+transac;
			window.open(url,"_ticket","");
			
			var elt = document.getElementById("billets"+manifid+"."+key);
			_closeBill(elt,"done");
			
			// passage au suivant
			e = document.getElementById('next');
			i = document.getElementById('sure');
			if ( e ) e.disabled = false;
			if ( i ) i.checked = true;
		}
		else alert("Impossible d'imprimer le(s) billet(s) -manque d'informations-");
	}
	
	function cleanNonValidated(form)
	{
		paras = form.getElementsByTagName("p");

		for ( i = 0 ; i < paras.length ; i++ )
		{
			para = paras.item(i);
			if ( para )
			if ( para.className == "content" )
			{
				inputs = para.getElementsByTagName("input");
				for ( j = 0 ; j < inputs.length ; j++ )
				{
					input = inputs.item(j);
					if ( input )
					if ( input.type == "hidden" )
					if ( input.parentNode.parentNode.className != "resume done" ) 
						input.parentNode.removeChild(input);
				}
			}
		}
	}
	
	function bill_addLine ( input, elt, refelt )
	{
		if ( input.value == "" )
			return false;
		
		newelt = elt.cloneNode(true);
		newelt.getElementsByTagName("select").item(0).selectedItem = 0;
		newelt.getElementsByTagName("input").item(0).value = "";
		input.removeAttribute("onblur");
		elt.parentNode.insertBefore(newelt,refelt);
	}

	function bill_showhide( elt )
	{
		alert(elt.className);
		elt.className = false;
	}
	
	function printAllOldManifs( elt )
	{
		// on annule le billet en base
		var xmlhttp = getHTTPObject();
		if ( xmlhttp )
		{
			url = "evt/bill/oldmanifs.hide.php";
							
			xmlhttp.open("GET",url,true);
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState == 4) /* 4 : état "complete" */
				if (xmlhttp.status == 200) /* 200 : code HTTP pour OK */
					elt.innerHTML = xmlhttp.responseText;
			}
			xmlhttp.send(null);
		}
	}

	function bill_jauge(manif)
	{
		var elt = document.getElementById("manif_"+manif);
		
		var xmlhttp = getHTTPObject();
		if ( xmlhttp && elt && elt.innerHTML == "" )
		{
			elt.innerHTML = '<span class="waiting">calcul en cours...</span>'
			url = "evt/bill/getjauge.hide.php?manifid="+manif;
			
			xmlhttp.open("GET",url,true);
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState == 4) /* 4 : état "complete" */
				if (xmlhttp.status == 200) /* 200 : code HTTP pour OK */
					elt.innerHTML = xmlhttp.responseText;
			}
			xmlhttp.send(null);
		}
	}

	function printDematerialized(transac)
	{
		window.location = "evt/bill/billing.php?t="+transac+"&s=3";
	}
