$(document).ready(function(){
  $('.record.csv-js').click(function(){
    if ( $(this).closest('.ui-widget-content').find('table').length == 0 )
    {
      console.error('No table found, unable to create a CSV file.');
      return false;
    }
    
    var obj = this;
    var csv = [];
    var metaobj = [''];
    if ( $(obj).closest('.ui-widget-content').find('table:first').find('tbody').length > 0 )
      metaobj = ['> thead', '> tbody', '> tfoot'];
    $.each(metaobj, function(i, search){
      $(obj).closest('.ui-widget-content').find('table:first '+search+' > tr').each(function(){
        var cells = [];
        $(this).find('th, td').each(function(){
          cells.push($(this).text());
        });
        csv.push(cells);
      });
    });
    
    $.each(csv, function(i, line){
      csv[i] = '"'+line.join('","')+'"';
    });
    var csvString = csv.join("\r\n");
    
    $(this).prop('href', URL.createObjectURL(new Blob([csvString], { type: 'text/csv' })))
    if ( "download" in document.createElement('a') )
      $(this).prop('download', $(this).closest('.ui-widget-header').find('h2').text().toLowerCase().replace(/ /g,'-')+'.csv');
  });
});
