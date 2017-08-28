$(document).ready(function(){

// play on canvas official size to avoid disproportions w/ picture
if ( LI.seatedPlanInitializationFunctions == undefined )
    LI.seatedPlanInitializationFunctions = [];
LI.seatedPlanInitializationFunctions.push(function(){
    $('.seated-plan canvas').each(function(){
        console.error($(this).width(), $(this).prop('width'));
        $(this)
            .prop('width', $(this).width())
            .prop('height', $(this).height())
        ;
    });
});

// draw the polygon
var plot = function(canvas, x, y, state) {
    if ( state == 'first' ) {
        canvas.fillStyle = 'red';
        canvas.beginPath();
        canvas.moveTo(x, y);
        if ( debug ) {
            console.error("c2.fillStyle = 'red';");
            console.error('c2.beginPath();');
            console.error('c2.moveTo('+x+', '+y+');');
        }
        return true;
    }
    
    canvas.lineTo(x, y);
    if ( debug ) {
        console.error('c2.lineTo('+x+', '+y+');');
    }
    
    if ( state == 'last' ) {
        canvas.closePath();
        canvas.fill();
        if ( debug ) {
            console.error('c2.closePath();');
            console.error('c2.fill();');
        }
    }
    
    return true;
}

var last = null;
var debug = location.hash == '#debug';
$('.seated-plan canvas').mouseup(function(e){ 
    var canvas = this.getContext('2d');
    
    if ( last === null ) {
        if ( e.button == 0 ) {
            plot(canvas, e.offsetX, e.offsetY, 'first');
            last = e;
        }
    }
    else {
        plot(canvas, e.offsetX, e.offsetY, e.button == 0 ? false : 'last');
        last = e.button == 0 ? e : null;
    }
    
    e.stopPropagation();
    return false;
});


});
