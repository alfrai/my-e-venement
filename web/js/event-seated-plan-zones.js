if ( LI == undefined )
    LI = {};

$(document).ready(function(){
    LI.seatedPlanZonesDrawing.setDebug();
    $('input.set_zones').click(function(){
        LI.seatedPlanZonesDrawing.setDebug();
        LI.seatedPlanZonesDrawing.clear();
        LI.seatedPlanZonesDrawing.resizeCanvas();
        $('#transition .close').click();
        
        // hide if not expected
        if ( !$(this).is(':checked') ) {
            $('.seated-plan canvas').hide();
            $(this).siblings('[name="clear-zones"]').hide();
            return;
        }
        
        // mouse action
        LI.seatedPlanZonesDrawing.activateDefinitionProcess();
        
        // clear zones
        $(this).siblings('button').show();
        
        // load zones
        LI.seatedPlanZonesDrawing.load();
    });
    
    $('.set-zones [name="clear-zones"]').click(function(){
        $.get($(this).attr('data-url'));
        LI.seatedPlanZonesDrawing.clear();
        return false;
    });
});

if ( LI.seatedPlanZonesDrawing == undefined ) {
    LI.seatedPlanZonesDrawing = { callbacks: [], points: [], exceptZones: [] };
}

LI.seatedPlanZonesDrawing.setDebug = function(debug){
    LI.seatedPlanZonesDrawing.debug = debug === undefined ? location.hash == '#debug' : debug;
}

LI.seatedPlanZonesDrawing.clear = function(){
    var canvas = $('.seated-plan canvas');
    canvas[0].getContext('2d').clearRect(0, 0, canvas.width(), canvas.height());
    LI.seatedPlanZonesDrawing.points = [];
}

LI.seatedPlanZonesDrawing.load = function(){
    LI.logIf(LI.seatedPlanZonesDrawing.debug, 'Seated plan: loading zones');
    $.ajax({
        url: $('.seated-plan canvas').attr('data-urls-get'),
        data: { except: LI.seatedPlanZonesDrawing.exceptZones },
        method: 'get',
        type: 'json',
        success: function(data){
            if ( data.type != 'zones' ) {
                LI.logIf(LI.seatedPlanZonesDrawing.debug, 'Seated plan: nothing to load... data is', data);
                return;
            }

            LI.logIf(LI.seatedPlanZonesDrawing.debug, 'Seated plan: this data is going to be loaded into the canvas:', data);
            
            var canvas = $('.seated-plan canvas')[0].getContext('2d');
            $.each(data.zones, function(zone_id, zone){
                for ( i = 0 ; i < zone.length ; i++ ) {
                    var type = false;
                    if ( i == 0 ) {
                        type = 'first';
                    }
                    else {
                        if ( i == zone.length - 1 ) {
                            type = 'lastauto';
                        }
                    }
                    LI.canvasPlot(canvas, zone[i].x, zone[i].y, type, data.color, zone_id);
                }
            });
        }
    });
}

LI.seatedPlanZonesDrawing.pointInPolygon = function(x, y, polygon){
    if ( polygon == undefined ) {
        return null;
    }
    
    var res = false;
    var j = polygon.length - 1;
    
    for ( var i = 0 ; i < polygon.length ; i++ ) {
        if ( ((polygon[i].y > y) != (polygon[j].y > y))
          && (x < (polygon[j].x - polygon[i].x) * (y - polygon[i].y) / (polygon[j].y - polygon[i].y) + polygon[i].x)
        ) {
            res = !res;
        }
        j = i;
    }
    
    return res;
}

// What to do after having loaded the zones
LI.seatedPlanZonesDrawing.loaded = function(){
    $('.seated-plan canvas.clickme').remove();
    $('.seated-plan canvas:first').each(function(){
        var canvas = this;
        $(this).clone().addClass('clickme')
            .insertAfter(this)
            .click(function(e){
                $.each(LI.seatedPlanZonesDrawing.points, function(zone_id, polygon){
                    LI.logIf(LI.seatedPlanZonesDrawing.debug, 'Test if a point is in a polygon', {x: e.offsetX, y: e.offsetY}, polygon, LI.seatedPlanZonesDrawing.pointInPolygon(e.offsetX, e.offsetY, polygon) ? 'inside' : 'outside');
                    if ( LI.seatedPlanZonesDrawing.pointInPolygon(e.offsetX, e.offsetY, polygon) ) {
                        if ( typeof(LI.window_transition) == 'function' ) {
                            LI.window_transition();
                        }
                        LI.seatedPlanZonesDrawing.exceptZones.push(zone_id);
                        $.get($(canvas).closest('.seated-plan').find('.seats-url').prop('href')+"&from_zone="+zone_id, function(data){
                            LI.logIf(LI.seatedPlanZonesDrawing.debug, 'loading seats in zone...', data);
                            LI.seatedPlanLoadDataRaw(data, true, null);
                            
                            $('#transition .close').click();
                        });
                    }
                });
            })
        ;
    });
}

/**
 * @param bool condition
 * @param [optional] string type (can be info, log, warn, error)
 * @param [multiple] data to log
 **/
LI.logIf = function() {
    var args = Array.prototype.slice.call(arguments);
    
    if ( !args.shift() ) {
        return false;
    }
    
    var type = 'error';
    if ( $.inArray(args[0], ['info', 'log', 'warn', 'error']) != -1 ){
        type = args.shift();
    }
    
    console[type].apply(null, args);
    
    return true;
}

// draw zones on each click
LI.seatedPlanZonesDrawing.activateDefinitionProcess = function(e){
    $('.seated-plan canvas')
        .css('display', 'block')
        .unbind('mouseup')
        .mouseup(function(e){ 
            var canvas = this.getContext('2d');
            
            if ( LI.seatedPlanZonesDrawing.last == undefined ) {
                if ( e.button == 0 ) {
                    LI.canvasPlot(canvas, e.offsetX, e.offsetY, 'first');
                    LI.seatedPlanZonesDrawing.last = e;
                }
            }
            else {
                LI.canvasPlot(canvas, e.offsetX, e.offsetY, e.button == 0 ? false : 'last');
                LI.seatedPlanZonesDrawing.last = e.button == 0 ? e : null;
            }
            
            e.stopPropagation();
            return false;
        })
    ;
        
}

LI.seatedPlanZonesDrawing.resizeCanvas = function(){
    $('.seated-plan canvas').each(function(){
        LI.logIf(LI.seatedPlanZonesDrawing.debug, 'Resize canvas', 'to', $(this).width(), 'from', $(this).prop('width'));
        
        $(this)
            .prop('width', $(this).width())
            .prop('height', $(this).height())
        ;
    });
}

// play on canvas official size to avoid disproportions w/ picture
if ( LI.seatedPlanInitializationFunctions == undefined )
    LI.seatedPlanInitializationFunctions = [];
LI.seatedPlanInitializationFunctions.push(LI.seatedPlanZonesDrawing.resizeCanvas);

// draw the polygon
LI.canvasPlot = function(canvas, x, y, state, color, zone_id) {
    if ( state == 'first' ) {
        canvas.fillStyle = color == undefined ? 'red' : color;
        canvas.beginPath();
        canvas.moveTo(x, y);
        if ( zone_id == undefined ) {
            LI.seatedPlanZonesDrawing.points.push([{ x: x, y: y }]);
        }
        else {
            LI.seatedPlanZonesDrawing.points[zone_id] = [{ x: x, y: y }];
        }
        
        LI.logIf(LI.seatedPlanZonesDrawing.debug, "c2.fillStyle = 'red';");
        LI.logIf(LI.seatedPlanZonesDrawing.debug, 'c2.beginPath();');
        LI.logIf(LI.seatedPlanZonesDrawing.debug, 'c2.moveTo('+x+', '+y+');');
        return true;
    }
    
    canvas.lineTo(x, y);
    LI.seatedPlanZonesDrawing.points[zone_id == undefined ? LI.seatedPlanZonesDrawing.points.length-1 : zone_id]
        .push({ x: x, y: y });
    LI.logIf(LI.seatedPlanZonesDrawing.debug, 'c2.lineTo('+x+', '+y+');');
    
    if ( state == 'last' || state == 'lastauto' ) {
        canvas.closePath();
        canvas.fill();
        LI.logIf(LI.seatedPlanZonesDrawing.debug, 'c2.closePath();');
        LI.logIf(LI.seatedPlanZonesDrawing.debug, 'c2.fill();');
    }
    
    if ( state == 'last' ) {
        if ( LI.seatedPlanZonesDrawing.callbacks.length > 0 ) {
            $.each(LI.seatedPlanZonesDrawing.callbacks, function(i, fct){
                fct();
            });
        }
    }
    
    return true;
}

// Default callback to record zones
LI.seatedPlanZonesDrawing.callbacks.push(function(){
    
    var json = JSON.stringify(LI.seatedPlanZonesDrawing.points);
    $.ajax({
        url: $('.seated-plan canvas').attr('data-urls-set'),
        method: 'post',
        data: {
            id: $('[name="seated_plan[id]"]').val(),
            zones: json
        }
    });
    
});
