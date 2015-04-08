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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/

jQuery.fn.toCSV = function(){
  var csv = [];
  jQuery(this).each(function(){
    if ( csv.length > 0 )
      csv.push([]); // a blank line as separator between tables
    
    var data = jQuery(this);
    var metaobj = [''];
    if ( data.find('tbody').length > 0 )
      metaobj = ['> thead', '> tbody', '> tfoot'];
    
    $.each(metaobj, function(i, search){
      data.find(search+' > tr').each(function(){
        var cells = [];
        jQuery(this).find('th, td').each(function(){
          cells.push(jQuery(this).text().replace(/"/g, '""'));
        });
        csv.push(cells);
      });
    });
  });
  
  $.each(csv, function(i, line){
    csv[i] = '"'+line.join('","')+'"';
  });
  return csv.join("\r\n");
}

jQuery.fn.downloadCSV = function(title){
  var csv = jQuery(this).toCSV();
  var url = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
  
  if ( !jQuery(this.context).is('a') )
  {
    window.location = url;
    return this;
  }
  
  var anchor = jQuery(this.context)
    .addClass('download-csv')
    .prop('href', url);
  if ( "download" in document.createElement('a') )
    anchor.prop('download', title.toLowerCase().replace(/ /g,'-')+'.csv');
  
  return this;
}
