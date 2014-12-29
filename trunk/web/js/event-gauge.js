// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {}

LI.renderGauge = function(item, only_inline_gauge, append_to)
{
  if ( only_inline_gauge == undefined )
    only_inline_gauge = false;
  
  // the small gauge
  if (!( typeof item != 'string' && $(item).find('.data .gauge.raw').length == 0 ))
  {
    data = JSON.parse(typeof item == 'string' ? item : $(item).find('.data .gauge.raw').text());
    var total = data.total > data.booked.printed + data.booked.ordered + data.booked.asked
      ? data.total
      : data.booked.printed + data.booked.ordered + data.booked.asked;
    $('#li_transaction_field_product_infos *').remove();
    $('<div></div>').addClass('gauge').addClass('raw')
      .appendTo(append_to ? append_to : $('#li_transaction_field_product_infos'))
      .append($('<span></span>').addClass('printed').css('width', (total > 0 ? data.booked.printed/total*100 : 100)+'%').html(data.booked.printed).prop('title',data.booked.printed))
      .append($('<span></span>').addClass('ordered').css('width', (data.booked.ordered/total*100)+'%').html(data.booked.ordered).prop('title',data.booked.ordered))
      .append($('<span></span>').addClass('asked')  .css('width', (data.booked.asked  /total*100)+'%').html(data.booked.asked).prop('title', data.booked.asked))
      .append($('<span></span>').addClass('free')   .css('width', ((data.free < 0 ? 0 : data.free)/total*100)+'%').html(data.free).prop('title',data.free))
      .prepend($('<span></span>').addClass('text').html('<span class="total">'+data.txt+'</span> <span class="details">'+data.booked_txt+'</span>'));
    ;
    $('#li_transaction_field_product_infos .gauge.raw > *').each(function(){
      if ( $(this).width() == 0 )
        $(this).hide();
    });
  }
  
  // gauge for seated plan
  if ( !only_inline_gauge && typeof item != 'string' && $(item).find('.data .gauge.seated').length > 0 )
  {
    if ( $(item).find('.data .gauge.seated.picture').length > 0 )
    {
      // cache
      $(item).find('.data .gauge.seated.picture').clone(true)
        .appendTo($('#li_transaction_field_product_infos'))
        .css('margin-bottom',(-$('#li_transaction_field_product_infos .gauge.seated.picture').height())+'px') // hack to avoid a stupid margin-bottom to be added
      ;
    }
    else
    {
      // remote loading
      var plan = $(item).find('.data .gauge.seated').clone(true).hide();
      plan.appendTo('#footer');
      //var scale = ($('#li_transaction_field_product_infos').width()-15)/plan.width();
      $(plan).addClass('picture').addClass('seated-plan')
        .appendTo($('#li_transaction_field_product_infos'))
        //.css('transform', 'scale('+scale+')') // the scale
      ;
      button = $('<button />')
        .html($('#li_transaction_field_close .show-seated-plan').text())
        .click(function(){
          LI.seatedPlanInitialization($('#li_transaction_field_product_infos'));
          $(this).hide();
        });
      $('<div />').addClass('show-seated-plan')
        .append(button)
        .appendTo($('#li_transaction_field_product_infos'));
      
      // caching
      LI.seatedPlanInitializationFunctions.push(function(){
        $(item).find('.data .gauge.seated.picture').remove(); // to ensure that we've got only one plan cached
        $('#li_transaction_field_product_infos .gauge.seated.picture')
          .show()
          .css('margin-bottom', 0)
          .clone(true).appendTo($(item).find('.data'));
      });
    }
  }
}
