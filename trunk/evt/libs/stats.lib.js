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
*    Copyright (c) 2006-2009 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
$(document).ready(function(){

$('form').each(function(){
  $(this).find('input[name=uri]').val($(this).find('img').attr('src'));
});

$('.stats.date form').submit(function(){
  uri = encodeURI($(this).find('input[name=uri]').val()+'?period='+this.period.value+'&from='+this.from.value);
  $(this).find('img').attr('src',uri);
  $(this).find('a').attr('href',uri+'&csv');
  return false;
});

$('.stats.interval form').submit(function(){
  uri = encodeURI($(this).find('input[name=uri]').val()+'?period='+this.period.value+'&start='+this.start.value+'&stop='+this.stop.value);
  $(this).find('img').attr('src',uri);
  $(this).find('a').attr('href',uri+'&csv');
  return false;
});

});
