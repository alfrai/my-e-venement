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
function get_nbcontingeants(span,manifid)
{
	var xmlhttp = getHTTPObject();
	if ( xmlhttp && span.innerHTML == ".." )
	{
		xmlhttp.open("GET","pro/jauge.hide.php?manifid="+manifid,true);
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState == 4) /* 4 : état "complete" */ {
			if ( elt = document.getElementById("waiting") )
				elt.className="";
			if (xmlhttp.status == 200) /* 200 : code HTTP pour OK */
			{
				var jauge = xmlhttp.responseText;
				span.innerHTML = jauge;
			}}
		}
		if ( elt = document.getElementById("waiting") )
			elt.className="show";
		xmlhttp.send(null);
	} // if ( xmlhttp )
}

