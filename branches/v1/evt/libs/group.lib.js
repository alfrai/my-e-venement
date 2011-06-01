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
	function createGroup(manifid,elt)
	{
		var xmlhttp = getHTTPObject();
		var wait;
		
		if ( xmlhttp )
		{
			url = "evt/infos/group.hide.php?ajax&manifid="+manifid;
							
			xmlhttp.open("GET",url,true);
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState == 4) /* 4 : état "complete" */
				{
					if (xmlhttp.status == 200 && xmlhttp.responseText == 'true') /* 200 : code HTTP pour OK */
						elt.className = "noerror ok";
					else	elt.className = "";
					if ( waiting ) waiting.className = "";
				}
			}
			if ( waiting = document.getElementById("waiting") )
				waiting.className = "show";
			xmlhttp.send(null);
		}
	}
