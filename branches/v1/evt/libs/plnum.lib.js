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
*    Copyright (c) 2006-2007 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
	// définitions du plan de salle
	function plnum_mousedown(event,elt)
	{
		if ( elt != document.getElementById("body") && elt != document )
		{
			document.mouseOffset = {x:event.layerX, y:event.layerY};
			document.movingTarget = elt;
			document.onmousemove = plnum_mousemove;
		}
		else if ( elt == document.getElementById("body") )
		{
			// rendu visible de l'espace sélectionné
			if ( !document.place.width.value || !document.place.height.value
			  || document.place.width.value == "0" || document.place.height.value == "0" )
			{
				area = document.getElementById("area");
				area.xpos = event.layerX;
				area.style.marginLeft = event.layerX+"px";
				area.ypos = event.layerY;
				area.style.marginTop = event.layerY+"px";
				area.className="visible";
				document.onmousemove = plnum_definearea;
			}
		}
		document.xmousedown = event.pageX;
		document.ymousedown = event.pageY;
	}
	
	function plnum_add(event)
	{				
		// renseignements complémentaires
		if ( document.place.placename.value == "" ) document.place.placename.value = 1;
		if ( document.place.rowname.value )
			plname = document.place.rowname.value+":"+document.place.placename.value;
		else	plname = document.place.placename.value;
		document.getElementById('num').innerHTML = plname;
		
		// récupération d'un élément "place"
		map = document.getElementById("mapping");
		sample = document.getElementById("placesample");
		place = sample.cloneNode(true);
		place.id = null;
		place.title = "num. "+plname;
		
		// ajout graphique le cas échéant // placement dans la page 
		place.style.width = document.place.width.value+'px'; 
		place.style.height = document.place.height.value+'px'; 
		place.style.marginLeft = Math.round(event.layerX-document.place.width.value/2)+'px'; 
		place.style.marginTop  = Math.round(event.layerY-document.place.height.value/2)+'px';
		
		// si placement forcé
		if ( document.getElementById("area").className != "" )
		{
			// disparition de area
			(area = document.getElementById("area")).className="";
			
			// nouvelle position...
			place.style.marginLeft = area.style.marginLeft;
			place.style.marginTop = area.style.marginTop;
		}
						
		// demande d'ajout en SGBD
		var alertbox = document.getElementById("alert");
		var xmlhttp = getHTTPObject();
		
		if ( alertbox )
		alertbox.className = "waiting";
		
		msg = "place ajoutée";
		url  = "evt/infos/plans/add.hide.php?";
		url +=  "siteid="+document.place.salleid.value;
		url += "&plname="+document.getElementById('num').innerHTML;
		url += "&onmap[x]="+place.style.marginLeft;
		url += "&onmap[y]="+place.style.marginTop;
		url += "&size[x]="+place.style.width;
		url += "&size[y]="+place.style.height;
		
		if ( xmlhttp && alertbox && url && msg && place)
		{
			xmlhttp.open("GET",url,true);
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState == 4) /* 4 : état "complete" */
				{
					if (xmlhttp.status == 200 || xmlhttp.status == 201 || xmlhttp.status == 202) /* 20*: code HTTP pour OK */
					{
						alertbox.innerHTML = msg;
						alertbox.className = "done";
						
						place.bdid = xmlhttp.responseText;
						map.appendChild(place);
						
						// renseignements complémentaires
						document.place.placename.value++;
					}
					else /* 412: erreur... precondition failed */
					{
						alertbox.className = "err";
						alertbox.innerHTML = xmlhttp.responseText;
					}
				}
			}
			xmlhttp.send(null);
		}
	}
	
	function plnum_dbmove(elt)
	{
		// récupération du numéro de la place
		id = elt.bdid;
		if ( !id && (span = elt.childNodes.item(0)) )
		if ( span.innerHTML != "" )
			id = span.innerHTML;
		if ( !id ) return false;
		
		// demande d'ajout en SGBD
		var alertbox = document.getElementById("alert");
		var xmlhttp = getHTTPObject();
		
		if ( alertbox )
		alertbox.className = "waiting";
		
		msg = "place déplacée";
		url  = "evt/infos/plans/mod.hide.php?";
		url +=  "id="+id;
		url += "&onmapx="+elt.style.marginLeft;
		url += "&onmapy="+elt.style.marginTop;
		
		if ( xmlhttp && alertbox && url && msg )
		{
			xmlhttp.open("GET",url,true);
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState == 4) /* 4 : état "complete" */
				{
					if (xmlhttp.status == 200 || xmlhttp.status == 201 || xmlhttp.status == 202) /* 20*: code HTTP pour OK */
					{
						alertbox.innerHTML = msg;
						alertbox.className = "done";
					}
					else /* 412: erreur... precondition failed */
					{
						alertbox.className = "err";
						alertbox.innerHTML = xmlhttp.responseText;
					}
				}
			}
			xmlhttp.send(null);
		}
	}
	
	function plnum_mouseup(event,elt)
	{
		if ( elt == document.getElementById("body") )
		{
			// définition d'un nouvel élément
			if ( document.place.width.value && document.place.height.value
			  && document.place.width.value != "0" && document.place.height.value != "0" )
			{
			  	if ( document.place.salleid.value )
				plnum_add(event);
			}
			// définition de la zone
			else
			{
				document.place.width.value = Math.abs(event.pageX - document.xmousedown);
				document.place.height.value = Math.abs(event.pageY - document.ymousedown);
				
				// dessin de la première zone
				plnum_mouseup(event,elt);
			}
		}
		// retour de drag&drop
		else
		{
			// fin du drag&drop sur l'objet
			if ( document.movingTarget )
			{
				// déplacement en base
				plnum_dbmove(document.movingTarget);
				document.movingTarget.onmousemove = null;
			}
			document.movingTarget = null;
			
			// fin de la sélection de zone area
			document.getElementById("area").className="";
		}
		
	}
	
	// définit la zone correspondant à la taille des places
	function plnum_definearea(event)
	{
		elt = document.getElementById("area");
		size = { x:event.pageX - document.xmousedown,
			 y:event.pageY - document.ymousedown};
		margin = { x:parseInt(elt.style.marginLeft),
			   y:parseInt(elt.style.marginTop) };
		
		// cas de sélection vers le haut ou la gauche
		if ( size.x < 0 )
		{
			size.x = -size.x;
			margin.x = elt.xpos - size.x;
		}
		if ( size.y < 0 )
		{
			size.y = -size.y;
			margin.y = elt.ypos - size.y;
		}
		
		elt.style.width = size.x+"px";
		elt.style.height = size.y+"px";
		elt.style.marginLeft = margin.x+"px";
		elt.style.marginTop = margin.y+"px";
	}
	
	// drag&drop
	function plnum_mousemove(event)
	{
		if ( document.movingTarget )
		{
			// le mouvement effectué
			dep = { x:(event.pageX - document.xmousedown), y:(event.pageY - document.ymousedown) };
			
			// le positionnement de départ
			pos = { x:parseInt(document.movingTarget.style.marginLeft),
				y:parseInt(document.movingTarget.style.marginTop) };
			
			// la relation entre les deux ... le déplacement
			document.movingTarget.style.marginLeft = (pos.x + dep.x)+"px";
			document.movingTarget.style.marginTop  = (pos.y + dep.y)+"px";
			
			// la MAJ des infos de départ
			document.xmousedown = document.xmousedown + dep.x;
			document.ymousedown = document.ymousedown + dep.y;
		}
	}
	
	// suppression d'un élément
	function plnum_delete(elt,id)
	{
		// suppression graphique
		if ( alertbox = document.getElementById("alert") )
		{
			var xmlhttp = getHTTPObject();
			
			alertbox.className = "waiting";
			
			msg = "place retirée";
			url  = "evt/infos/plans/del.hide.php?";
			if ( id )
				url += "id="+id;
			else	url += "id="+elt.bdid;
			
			if ( xmlhttp && alertbox && url && msg )
			{
				xmlhttp.open("GET",url,true);
				xmlhttp.onreadystatechange=function()
				{
					if (xmlhttp.readyState == 4) /* 4 : état "complete" */
					{
						if (xmlhttp.status == 200 || xmlhttp.status == 201 || xmlhttp.status == 202) /* 20*: code HTTP pour OK */
						{
							alertbox.innerHTML = msg;
							alertbox.className = "done";
							elt.parentNode.removeChild(elt);
						}
						else /* 412: erreur... precondition failed */
						{
							alertbox.className = "err";
							alertbox.innerHTML = xmlhttp.responseText;
						}
					}
				}
				xmlhttp.send(null);
			}
		}
	}
	
	// autre
	function plnum_verif(input,manifid,defaultValue,id)
	{
		prefixe = "";
		if ( input.className == "exemple" )
			prefixe = "exemple ";
		errorClass = "error";
		readyClass = "ready";
		
		var xmlhttp = getHTTPObject();
		if ( xmlhttp && input )
		if ( input.value != defaultValue && input.value != "" )
		{
			url = "evt/bill/verifplnum.hide.php?manifid="+manifid+"&plnum="+input.value+"&id="+id;
			
			xmlhttp.open("GET",url,true);
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState == 4) /* 4 : état "complete" */
				if (xmlhttp.status == 200) /* 200 : code HTTP pour OK */
				{
					if ( xmlhttp.responseText != "true" )
						input.className = prefixe+errorClass;
					else
					{
						// vérification que la place n'a pas déjà été choisie dans la procédure courante sans avoir été imprimée
						inputs = input.parentNode.parentNode.parentNode.getElementsByTagName('input');
						ret = true;
						for ( i = 0 ; elt = inputs.item(i) ; i++ )
						if ( elt.type == "text" && elt.value == input.value && elt != input )
						{
							ret = false;
							break;
						}
						
						if ( ret )
							input.className = prefixe+readyClass;
						else	input.className = prefixe+errorClass;
					}
				}
				else	input.className = prefixe+errorClass;
			}
			xmlhttp.send(null);
		}
	}

	function plnum_selectplace(elt,manifid)
	{
		// vérif à faire sur le tarif donné
		
		if ( elt.className == "place selected" )
		{
			// désélection d'une place
			elt.removeChild(elt.lastChild);
			elt.className = "place";
		}
		else if ( tarif = document.place.resa.value )
		{
			// sélection d'une place
			input = document.createElement("input");
			input.type	= "hidden";
			input.name	= "billet["+manifid+"][]";
			input.value	= tarif+":plnum-"+elt.firstChild.innerHTML; // la convention veut que les places numérotées aient leur numéro précédées de "plnum-" dans le champ other récup par preg_tarif
			
			elt.appendChild(input);
			elt.className += " selected";
		}
	}
