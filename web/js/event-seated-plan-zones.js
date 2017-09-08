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
            $(this).siblings('[name="clear-zones"], .help').hide();
            return;
        }
        
        // mouse action
        LI.seatedPlanZonesDrawing.activateDefinitionProcess();
        
        // show buttons & helpers
        $(this).siblings('[name="clear-zones"], .help').show();
        
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

LI.seatedPlanZonesDrawing.clear = function(canvas){
    LI.seatedPlanZonesDrawing.log('Seated plan: clearing canvas.');
    
    if ( canvas == undefined ) {
        canvas = $('.seated-plan canvas');
    }
    
    $(canvas)[0].getContext('2d').clearRect(0, 0, $(canvas).width(), $(canvas).height());
    LI.seatedPlanZonesDrawing.points = [];
}

LI.seatedPlanZonesDrawing.load = function(){
    if ( LI.seatedPlanZonesDrawing.zones != undefined ) {
        LI.seatedPlanZonesDrawing.log('Seated plan: zones are already loaded, then redraw & dispatch zones');
        LI.seatedPlanZonesDrawing.drawZones();
        return;
    }
    
    LI.seatedPlanZonesDrawing.log('Seated plan: loading zones');
    $.ajax({
        url: $('.seated-plan canvas').attr('data-urls-get'),
        method: 'get',
        type: 'json',
        success: function(data){
            if ( data.type != 'zones' ) {
                LI.seatedPlanZonesDrawing.log('Seated plan: nothing to load... data is', data);
                return;
            }
            
            LI.seatedPlanZonesDrawing.log('Seated plan: this data is going to be loaded into the canvas:', data);
            LI.seatedPlanZonesDrawing.zones = data.zones;
            LI.seatedPlanZonesDrawing.drawZones();
        }
    });
}

LI.seatedPlanZonesDrawing.drawZones = function(){
    var canvas = $('.seated-plan canvas:first');
    var context;
    var c2 = false;
    LI.seatedPlanZonesDrawing.clear(canvas);
    
    $.each(LI.seatedPlanZonesDrawing.zones, function(zone_id, zone){
        if ( $.inArray(zone.id, LI.seatedPlanZonesDrawing.exceptZones) === -1 ) {
            context = canvas[0].getContext('2d');
            LI.seatedPlanZonesDrawing.drawZone(zone, context);
        }
        
        // if a first pass have been already done to create the "under-seats" zones, then exceptZones > 0
        if ( LI.seatedPlanZonesDrawing.exceptZones.length > 0 ) {
            return;
        }
        
        LI.seatedPlanZonesDrawing.log('Seated plan: add a canvas under defined seats');
        c2 = $('.seated-plan canvas.under-seats[data-spid='+zone.seated_plan_id+']');
        if ( c2.length == 0 ) {
            c2 = $(canvas).clone()
                .removeClass('visible')
                .addClass('under-seats')
                .attr('data-spid', zone.seated_plan_id)
                .insertAfter(canvas)
            ;
            LI.seatedPlanZonesDrawing.log('creates a sub-seats canvas');
        }
        context = c2[0].getContext('2d');
        LI.seatedPlanZonesDrawing.drawZone(zone, context);
    });
    
    return canvas;
}

LI.seatedPlanZonesDrawing.drawZone = function(zone, context){
    for ( i = 0 ; i < zone.polygon.length ; i++ ) {
        var type = false;
        if ( i == 0 ) {
            type = 'first';
        }
        else {
            if ( i == zone.polygon.length - 1 ) {
                type = 'lastauto';
            }
        }
        LI.canvasPlot(context, zone.polygon[i].x, zone.polygon[i].y, type, zone.color, zone.id);
    }
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
    var canvas = $('.seated-plan canvas:first');
    var tos = [];
    canvas.addClass('light');
    canvas.clone()
        .addClass('clickme')
        .insertAfter(canvas)
        .mousemove(function(event){
            var e = event;
            var elt = this;
            for ( var i = 0 ; i < tos.length ; i++ ) { clearTimeout(tos[i]); } // clear previous timeouts
            
            tos.push(setTimeout(function(){
                if ( LI.seatedPlanZonesDrawing.zones == undefined ) {
                    return;
                }
                
                var hover;
                $.each(LI.seatedPlanZonesDrawing.zones, function(zone_id, zone){
                    hover = LI.seatedPlanZonesDrawing.pointInPolygon(e.offsetX, e.offsetY, zone.polygon);
                    if ( hover ) {
                        if ( $.inArray(zone_id, LI.seatedPlanZonesDrawing.exceptZones) == -1 ) {
                            $(elt).addClass('hover');
                        }
                        var spid = LI.seatedPlanZonesDrawing.zones[zone_id].seated_plan_id;
                        $('.seated-plan canvas.under-seats.visible').removeClass('visible');
                        $('.seated-plan canvas.under-seats[data-spid='+spid+']').addClass('visible');
                        LI.seatedPlanZonesDrawing.log('Seated Plans: mouse over a zone', zone_id, 'of this plan', spid);
                    }
                    return !hover;
                });
                
                if ( !hover ) {
                    $('.seated-plan canvas.under-seats.visible').removeClass('visible');
                    $(elt).removeClass('hover');
                }
            }, 100));
        })
        .mousemove()
        .mouseout(function(e){
            $(this).removeClass('hover');
            $('.seated-plan canvas.under-seats.visible').removeClass('visible');
        })
        .click(function(e){
            $.each(LI.seatedPlanZonesDrawing.zones, function(zone_id, zone){
                LI.seatedPlanZonesDrawing.log('Test if a point is in a zone', {x: e.offsetX, y: e.offsetY}, zone.polygon, LI.seatedPlanZonesDrawing.pointInPolygon(e.offsetX, e.offsetY, zone.polygon) ? 'inside' : 'outside');
                if ( !LI.seatedPlanZonesDrawing.pointInPolygon(e.offsetX, e.offsetY, zone.polygon) ) {
                    return;
                }

                if ( typeof(LI.window_transition) == 'function' ) {
                    LI.window_transition();
                }
                
                if ( $.inArray(zone.id, LI.seatedPlanZonesDrawing.exceptZones) == -1 ) {
                    LI.seatedPlanZonesDrawing.exceptZones.push(zone.id);
                }
                
                if ( LI.seatedPlanZonesDrawing.points[zone.id] != undefined ) {
                    LI.seatedPlanZonesDrawing.log('The zone still needs to be drawn');
                    $.get(canvas.closest('.seated-plan').find('.seats-url').prop('href')+"&from_zone="+zone.id, function(data){
                        LI.seatedPlanZonesDrawing.log('loading seats in zone...', data);
                        $('#transition .close').click();
                        
                        LI.seatedPlanLoadDataRaw(data, true, null);
                        LI.seatedPlanZonesDrawing.drawZones();
                    });
                }
                
                var sp = $('.seated-plan.picture');
                
                // zoom
                var magnify = sp.closest('.gauge').find('.magnify .magnify-in');
                var scales = { init: sp.attr('data-scale-init'), current: sp.attr('data-scale') };
                var zoom = function(){
                    if ( scales.current >= scales.init*1.3*1.3*1.3) {
                        return 1;
                    }
                    if ( scales.current >= scales.init*1.3*1.3 ) {
                        magnify.click();
                        return 1.25;
                    }
                    if ( scales.current >= scales.init*1.3 ) {
                        magnify.click();
                        magnify.click();
                        return 1.48;
                    }
                    if ( scales.current >= scales.init ) {
                        magnify.click();
                        magnify.click();
                        magnify.click();
                        return 1.70;
                    }
                    magnify.click();
                    magnify.click();
                    magnify.click();
                    magnify.click();
                    return 1.90;
                }
                var coef = zoom();
                coef = coef > 1 ? coef : 1;
                LI.seatedPlanCenterScroll(sp.parent(), e, coef);
                
                return false;
            });
        })
    ;
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

LI.seatedPlanZonesDrawing.log = function(){
    var args = Array.prototype.slice.call(arguments);
    args.unshift(LI.seatedPlanZonesDrawing.debug);
    return LI.logIf.apply(null, args);
}

// draw zones on each click
LI.seatedPlanZonesDrawing.activateDefinitionProcess = function(e){
    $('.seated-plan canvas:first')
        .addClass('visible')
        .unbind('mouseup')
        .mouseup(function(e){ 
            var canvas = this.getContext('2d');
            
            if ( e.button != 0 ) {
                return false;
            }
            
            if ( LI.seatedPlanZonesDrawing.last == undefined || !(e.ctrlKey && e.shiftKey) ) {
                // set a new edge
                LI.canvasPlot(canvas, e.offsetX, e.offsetY, LI.seatedPlanZonesDrawing.last == undefined ? 'first' : false);
                LI.seatedPlanZonesDrawing.last = e;
            }
            else {
                // define the last edge and close the path
                LI.canvasPlot(canvas, e.offsetX, e.offsetY, 'last');
                LI.seatedPlanZonesDrawing.last = null;
            }
            
            e.stopPropagation();
            return false;
        })
    ;
        
}

LI.seatedPlanZonesDrawing.resizeCanvas = function(){
    $('.seated-plan canvas').each(function(){
        if (!( $(this).prop('width') == $(this).width() && $(this).prop('height') == $(this).height() )) {
            LI.seatedPlanZonesDrawing.log('Resize canvas', 'to', $(this).width(), 'from', $(this).prop('width'));
            $(this)
                .prop('width', $(this).width())
                .prop('height', $(this).height())
            ;
        }
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
        
        LI.seatedPlanZonesDrawing.log("c2.fillStyle = '"+color+"';");
        LI.seatedPlanZonesDrawing.log('c2.beginPath();');
        LI.seatedPlanZonesDrawing.log('c2.moveTo('+x+', '+y+');');
        return true;
    }
    
    canvas.lineTo(x, y);
    canvas.stroke();
    LI.seatedPlanZonesDrawing.points[zone_id == undefined ? LI.seatedPlanZonesDrawing.points.length-1 : zone_id]
        .push({ x: x, y: y });
    LI.seatedPlanZonesDrawing.log('c2.lineTo('+x+', '+y+');');
    
    if ( state == 'last' || state == 'lastauto' ) {
        canvas.closePath();
        canvas.fill();
        LI.seatedPlanZonesDrawing.log('c2.closePath();');
        LI.seatedPlanZonesDrawing.log('c2.fill();');
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
