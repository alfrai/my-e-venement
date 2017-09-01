LI.validateCP = function() {
    if ($('#transaction_postalcode').val().length == 0 &&
        $('#li_transaction_field_informations .adr .postal-code').text().length == 0)
    {
       LI.alert($('#li_transaction_field_close .print .CP-print-error').html());
       return false;
   } 
}

$(document).ready(function(){
    //$('#li_transaction_manifestations .footer .print').submit(function() {
    $('#print-tickets').click(function() {
        return LI.validateCP();
    });
    $('#li_transaction_field_simplified .print button').click(function() {
        return LI.validateCP();
    });
    
});