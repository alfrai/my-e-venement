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
var manif = 0;
var client = 0;
var origelt;
var bufelt;

function sco_newmanif(value,tabid)
{
	manif++;
	var tmp = manif;
	
	select = document.getElementById("newmanif");
	
	var xmlhttp = getHTTPObject();
	if ( xmlhttp && select )
	{
		xmlhttp.open("GET","sco/manifs.hide.php?s="+value+"&id="+tabid,true);
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState == 4) /* 4 : état "complete" */
			if (xmlhttp.status == 200) /* 200 : code HTTP pour OK */
			{
				if ( tmp < manif )
					return false;
				
				select.innerHTML = xmlhttp.responseText;
			}
		}
		if ( elt = document.getElementById("waiting") )
			elt.className="show";
		window.setTimeout(function(){
			if ( tmp >= manif ) xmlhttp.send(null);
		},700);
	} // if ( xmlhttp )
}

function sco_annu(value)
{
	client++;
	var tmp = client;
	
	select = document.getElementById("newclient");
	
	var xmlhttp = getHTTPObject();
	if ( xmlhttp && select && value != "" )
	{
		xmlhttp.open("GET","ann/process.php?more&s="+value);
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState == 4) /* 4 : état "complete" */
			if (xmlhttp.status == 200) /* 200 : code HTTP pour OK */
			{
				if ( tmp < client )
					return false;
				
				var xmldoc = xmlhttp.responseXML;
				//alert(xmlhttp.responseText);
				
				list = "";
				if ( xmldoc.getElementsByTagName("ppl").length > 0 )
				{
					ppl = xmldoc.getElementsByTagName("ppl").item(0).getElementsByTagName("element");
					for ( var i = 0 ; i < ppl.length ; i++ )
					{
						id      = ppl.item(i).getAttribute("value");
						nom     = ppl.item(i).getElementsByTagName("nom").item(0).firstChild.data;
						prenom  = ppl.item(i).getElementsByTagName("prenom").item(0).firstChild.data;
						orgid   = ppl.item(i).getElementsByTagName("orgid").item(0).firstChild.data;
						orgnom  = ppl.item(i).getElementsByTagName("orgnom").item(0).firstChild.data;
						fctdesc = ppl.item(i).getElementsByTagName("fctdesc").item(0).firstChild.data;
						fctid   = ppl.item(i).getElementsByTagName("fctid").item(0).firstChild.data;
						if ( ppl.item(i).getElementsByTagName("npai").item(0).firstChild.data == "true" )
							class   = "npai";
						else    class   = "";
						
						list += '<option class="pers '+class+'" value="';
						if ( fctid > 0 )
							list += 'prof_'+fctid;
						else    list += 'pers_'+id;
						list += '">';
						list += nom+' '+prenom;
						if ( orgid > 0 )
						{
							list += ' ('+orgnom;
							if ( trim(fctdesc) != "" ) list += ' - '+fctdesc;
							list += ')';
						}
						list += '</option>';
					}
				}
				
				document.getElementById("newclient").innerHTML = list;
			}
		}
		window.setTimeout(function(){
			if ( tmp >= client ) xmlhttp.send(null);
		},700);
	}
}

function sco_highlight(elt,checked)
{
	if ( checked )	elt.className += " highlight";
	else		elt.className = elt.className.replace(/highlight/g,'');
}
function sco_secondchoice(elt,checked)
{
	if ( checked )	elt.className += " secondchoice";
	else		elt.className = elt.className.replace(/secondchoice/g,'');
}

function sco_newticket(elt)
{
	r = 0;
	if ( elt.value != "" && elt.parentNode.getElementsByTagName('span').length == 1 )
	{
		// controle qu'il n'y a pas déjà un input vide
		add = true;
		if ( inputs = elt.parentNode.getElementsByTagName('input') )
		for ( var i = 0 ; i < inputs.length ; i++ )
		if ( inputs.item(i).type == "text" && inputs.item(i).value == "" )
		{
			add = false;
			break;
		}
		
		if ( add )
		{
			newtick = elt.cloneNode(true);
			elt.parentNode.insertBefore(newtick,elt.parentNode.getElementsByTagName('span').item(0));
			newtick.value = "";
			newtick.focus();
			r = 1;
		}
	}
	else if ( (inputs = elt.parentNode.getElementsByTagName("input")).length > 1 )
	{
		elt.parentNode.removeChild(elt);
		r = -1;
		inputs.item(inputs.length-1).focus();
	}
	return r;
}

function sco_disableinputs(htmlelt)
{
	inputs = htmlelt.getElementsByTagName('input');
	for ( var i = 0 ; i < inputs.length ; i++ )
	if ( inputs.item(i).className != "confirmed" )
		inputs.item(i).disabled = true;
}

function sco_cut(elt,class)
{
	// le buffer
	bufelt = elt.cloneNode(true);
	
	// l'apparence
	document.body.className = "cutted";
	elt.className = class;
	
	// la RAZ des données coupées
	inputs = elt.getElementsByTagName("input");
	text = 0;
	for ( var i = 0 ; i < inputs.length ; i++ )
	{
		input = inputs.item(i);
		if ( input.type == "text" )
		{
			if ( text > 0 )
			{
				input.parentNode.removeChild(input);
				i--;
			}
			else	input.value = "";
			text++;
		}
		else if ( input.type == "checkbox" )
			input.checked = false;
	}
}

function sco_paste(elt,class)
{
	if ( bufelt )
	{
		binputs = bufelt.getElementsByTagName('input');
		oinputs = elt.getElementsByTagName('input');
		check = 0;
		
		// clean des inputs(text) d'origine, préparation du "coller"
		for ( var i = 0 ; i < oinputs.length ; i++ )
		{
			input = oinputs.item(i);
			if ( input.type == 'text' )
			{
				input.value = '';
				i += sco_newticket(input);
			}
		}
		
		// "coller" à proprement parlé
		for ( i = 0 ; i < binputs.length && i < oinputs.length ; i++ )
		{
			binput = binputs.item(i);
			oinput = oinputs.item(i);
			
			if ( binput.type == 'text' && oinput.type == 'text' && binput.value != "" )
			{
				oinput.value = binput.value;
				sco_newticket(oinput);
			}
			else if ( binput.type == 'checkbox' && oinput.type == 'checkbox' )
			{
				oinput.checked = binput.checked;
				if ( check == 0 )
					sco_secondchoice(elt,oinput.checked);
				else if ( check == 1 )
					sco_highlight(elt,oinput.checked);
				check++;
			}
		}
	}
}

$(document).ready(function(){
  $('.entry #first-line .titre').mouseover(function(){
    $('#reminder').show();
  });
  $('.entry #first-line .titre').mouseout(function(){
    $('#reminder').hide();
  });
});
