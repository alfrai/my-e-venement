<?php // use_stylesheet('/css/default.css');  ?>
<?php use_stylesheet('/css/event.css'); ?>
<?php // use_stylesheet('/css/manifestation.css');  ?>
<?php //use_stylesheet('/css/view.css'); ?>
<?php use_javascript('/sfAdminThemejRollerPlugin/js/jquery-ui.custom.min.js'); ?>
<?php use_stylesheet('/css/customize/customize.css') ?>
<?php use_javascript('/js/customize/fabric.js') ?>
<?php //use_javascript('/js/customize/jquery-ui.drag.js') ?>



<div id="sf_admin_container" class="sf_admin_edit ui-widget ui-corner-all">

    <div class="fg-toolbar ui-widget-header ui-corner-all">
        <div style="display: inline;"><h1>ticket templating</h1></div>
        
    </div>
    <div id="flash" class="sf_admin_flashes ui-widget">

    </div>
    <div id="sf_admin_header">
        
    </div>

    <div id="sf_admin_content">

        <div class="sf_admin_form">
            <form method="post" autocomplete="off" action="" enctype="multipart/form-data">


                
                <div class="sf_admin_actions_block ui-widget ui-widget-content" style="float: left;">
                    
                    <ul class="sf_admin_actions_form">
                        <li><button class="fg-button ui-state-default fg-button-icon-left" id="print" type="button"><span class="ui-icon ui-icon-print"></span>test print</button></li>
                        <li><a href="<?php echo url_for('ticket/customizeSave') ?>" class="fancybox"><button class="fg-button ui-state-default fg-button-icon-left" id="serializer" type="button"><span class="ui-icon ui-icon-circle-check"></span>save template</button></a></li>
                        <li><button class="fg-button ui-state-default fg-button-icon-left" id="back" type="button"><span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>back</button></li>
                    </ul>
<!--            TODO-->
<!--            <button class="fg-button ui-state-default" id="reset" display="inline-block;">reset</bouton>-->
                </div>
                <div class="ui-helper-clearfix"></div>
                <div id="sf_admin_form_tab_menu" class="ui-tabs ui-widget ui-widget-content ui-tabs-vertical ui-helper-clearfix">
                   
                    <?php include_component('ticket', 'customTckMenu', array('json' => $json)) ?>
                    
                </div>
                
            </form>
           
             
        </div>
        <div class ="objectControl ui-widget ui-corner-all">
            
        <div id="canControls" class="ui-widget-content" style="padding:3px;">
            
            <p>Select an object on the template to activate controls below</p>
            <span id='flashCanvas' style="color: red; opacity: 0;">Warning text can be inserted here</span>
            <div id="text-controls" class="toolbar ui-controlgroup ui-controlgroup-horizontal ui-helper-clearfix" role="toolbar">
                <button type="button" class="btn-object-remove ui-controlgroup-item" id="object-remove">Remove</button>
                <label for="font-family">Font type:</label>
                <select id="font-family" class="slct-object-action ui-controlgroup-item" data-property="fontFamily">
                    <?php 
                        
                        foreach ($font as $key => $value) {
                            echo('<option value="'.$key.'" style="font-family:'.$key.';">'.$value.'</option>');
                        }
                    ?>
                </select>
                
                <label for="text-align" hidden>Text align:</label>
                <select id="text-align" class="slct-object-action ui-controlgroup-item" data-property="textAlign" hidden>
                    <option value="Left">Left</option>
                    <option value="Center">Center</option>
                    <option value="Right">Right</option>
                    <option value="Justify">Justify</option>
                </select>
                
                    <label for="text-font-size">Font size:</label>
                    <input value="" min="6" max="120" step="1" id="text-font-size" class="sldr-object-action ui-controlgroup-item" data-property="fontSize" type="range">
                
                    <label for="text-line-height" style="display:none">Line height:</label>
                    <input value="" min="1" max="5" step="0.1" id="text-line-height" class="sldr-object-action ui-controlgroup-item" data-property="lineHeight" type="range" hidden>
                
                    <label for="text-char-spacing">Char spacing:</label>
                    <input value="" min="-100" max="800" step="10" id="text-char-spacing" class="sldr-object-action ui-controlgroup-item" data-property="charSpacing" type="range">
                
                <button type="button" class="btn-object-action ui-controlgroup-item" id="text-cmd-bold" data-property="fontWeight" data-value="bold">Bold</button>
                <button type="button" class="btn-object-action ui-controlgroup-item" id="text-cmd-italic" data-property="fontStyle" data-value="italic">Italic</button>
                <button type="button" class="btn-object-action ui-controlgroup-item" id="text-cmd-underline" data-property="textDecoration" data-value="underline">Underline</button>
                <button type="button" class="btn-object-action ui-controlgroup-item" id="text-cmd-linethrough" data-property="textDecoration" data-value="line-through" hidden>Linethrough</button>
                <button type="button" class="btn-object-action ui-controlgroup-item" id="text-cmd-overline" data-property="textDecoration" data-value="overline" hidden>Overline</button>
            </div>
            
        </div>
        </div>
    </div>
    <div id="templating">
        
    <div class="canvas">
        <!-- TODO set values through PHP -->
        <canvas id="tktCanvas" width="" height="" style="">

        </canvas>
    </div>
    <div id="inputOnTemplate" style="visibility: collapse;"></div>
    </div>
    <div id="sf_admin_footer">
        The current templating mode is delivered as is. The final user stays responsable of creating a template that respects the laws and regulations concerning his use of tickets.
        Moreover, if the user edits this application's code in any way, it will set free the editor of all legal implications.
        We strongly encourage you to test a template before using it, and to refer to your supplier or any professional in case of doubt.
    </div>
</div>

    <div class="">
        <form id="tckTemplate" method="post" autocomplete="off" action="<?php echo url_for('ticket/customizeSave') ?>" enctype="multipart/form-data">

            <button class="fg-button ui-state-default" id="save" type="button" hidden="true">save</button>
            <!-- TODO set values through PHP -->
            <input type="text" name="name" id="name" value="<?php echo(str_shuffle('le petit chat est mort'))?>" hidden>
            <input type="text" name="controller" id="controller" value="<?php echo($controller)?>" hidden>
            <input type="number" name="contrlWidth" id="contrlWidth" value="<?php echo($tckSize['controller'])?>" hidden>
            <input type="number" name="event_id" id="event_id" value="<?php echo($eventId) ?>" hidden>
            <input type="text" name="dataJson" id="dataJson" hidden>
            <input type="text" name="dataSvg" id="dataSvg" value="" hidden>
            <input type="text" name="description" id="description" hidden>
            <input type="number" name="ticketHeight" id="ticketHeight" value="<?php echo($tckSize['height'])?>" hidden>
            <input type="number" name="ticketWidth" id="ticketWidth" value="<?php echo($tckSize['width'])?>" hidden>
            <input type="text" name="type" id="type" value="" hidden>
        </form>
    </div>
<div id="transition" class="close"><span class="close"></span></div>
<script type="text/javascript">
    
    
    //set the size of canvas according to full size of user screen
    var scrWidth = screen.width;
    var tckRatio = $('#ticketHeight').val()/$('#ticketWidth').val();
    //not working, hard coded for now
    var tckWidth = 1000;
    var pxRatio = tckWidth/$('#ticketWidth').val();
    //$('#tktCanvas').attr('width', tckWidth).attr('height', tckWidth*tckRatio);
    
    fabric.devicePixelRatio = 1;
    //instance of canvas with fabric
    var canvas = new fabric.Canvas('tktCanvas');
    canvas.setHeight(tckWidth*tckRatio);
    canvas.setWidth(tckWidth);
    document.getElementById("tktCanvas").height = tckWidth*tckRatio;
    document.getElementById("tktCanvas").width = tckWidth;
    var canvasWidth = canvas.getWidth(),
            canvasHeight = canvas.getHeight();
    canvas.setBackgroundColor('white');
    //too many unpredictable side effects with groups
    //fabric.Group.prototype.hasControls = false;
    canvas.selection = false;
    
    
    var controller = $('#controller').val();
    //if controller, add a virtual rect to bound objects in
    var rectControl;
    var containWidth = tckWidth;
    var containStart = 0,
            controlStart = 0;
            pxCtrlWidth = 0;
    if (controller!==''){
        //setting all the param, taking care of the R L position of the controller
        var pxCtrlWidth = pxRatio*$('#contrlWidth').val();
        containWidth = tckWidth - pxCtrlWidth;
            //starting point of the controller
        
        if (controller=='L'){
            containStart = pxCtrlWidth;
        //only right or left for now, but add here other cases (upper, down, etc(?))
        }else{
            controlStart = tckWidth - pxCtrlWidth;
        }
        rectControl = {'TL': new fabric.Point(controlStart,0),
                       'BR': new fabric.Point(controlStart+pxCtrlWidth,canvasHeight)
                        };
        var contLine = new fabric.Line([Math.max(controlStart, containStart),0,Math.max(controlStart, containStart),canvasHeight],{
                stroke: 'blue', strokeWidth: 3, opacity:0.3, selectable: false, excludeFromExport: true
        });
        canvas.add(contLine);
    }
    //in any case, add a container
    var rectMain = {'TL': new fabric.Point(containStart,0),
                       'BR': new fabric.Point(containStart+containWidth,canvasHeight)
                    };
    
    var grid = 10;
    // create grid
    for (var i = 0; i < (canvasWidth / grid); i++) {
      canvas.add(new fabric.Line([ i * grid, 0, i * grid, canvasHeight], {  
                              stroke: '#ccc', 
                              selectable: false , 
                              excludeFromExport: true,
                              opacity: 0.6
                            }));
    };
    for (var j = 0; j < (canvasHeight / grid); j++){
        canvas.add(new fabric.Line([ 0, j * grid, canvasWidth, j * grid], { 
                                stroke: '#ccc',
                                selectable: false, 
                                excludeFromExport: true,
                                opacity: 0.6
                            }));
    };

    var TckLabel = fabric.util.createClass(fabric.Text, {
        initialize: function (text2Display, options) {
            this.callSuper('initialize', text2Display, options);
            options && this.set('name', options.name);
            this.set('hasControls', false);
            this.set('hasRotatingPoint', false);
            this.set('lockRotation', true);
            this.set('fontFamily', 'arial');
            this.set('charSpacing', '0');
            this.set('padding', '-3');
            this.set('id', options.id);
            this.set('container', options.container);

        },
        //to factorize when printing is fully tested
        toObject: function () {
            var mustachText = '{{'+this.get('name')+'}}';
            var svgClone = fabric.util.object.clone(this);
            svgClone.set('text',mustachText);
            return fabric.util.object.extend(svgClone.callSuper('toObject'), {name: this.name});
        },
        toSVG: function () {
            var mustachText = '{{'+this.get('name')+'}}';
            var svgClone = fabric.util.object.clone(this);
            svgClone.set('text',mustachText);
            //svgClone.set('id', this.get('name'));
            return fabric.util.object.extend(svgClone.callSuper('toSVG'));
        }
        
    });


    //SCRIPT FOR THE INTERACTIONS

    //menu
    
    //bad should be removed after testing
    function changeState(target)
    {
        var checkedClass = "checked";
        target.toggleClass(checkedClass);
        if (target.hasClass(checkedClass)) {
            addItem2Canvas(target);
        } else {
            removeItemFromCanvas(target);
        }
    }
    
    // keeping tracks of all items
    var itemsOnCanvas = [];
    
    function removeItemFromCanvas(target) {
//        console.log(target);
//        var id2remove = target.attr('id');
//        var myObj = canvas.getObjects();
//        var index = myObj.findIndex(obj => (obj.id === id2remove));
        console.log(itemsOnCanvas);
        canvas.remove(target);
        var toRemove = $('#templating #inputOnTemplate [id="'+target.id+'"]');
        console.log(toRemove);
        toRemove.detach();
        console.log(itemsOnCanvas);
        var index = itemsOnCanvas.findIndex(function(elm){return (elm.id == target.id);});
        console.log(index);
        itemsOnCanvas.splice(index,1);
        console.log(itemsOnCanvas);

    }
    
    //TODO generalize for any TYPE
    function buildTextObject(target, contain){
        var targetLabel = target.attr("value");
        var targetName = target.attr("name");
        var targetId = target.attr("id");
        var targetFontType = "Arial";
        var targetFontSize = 16;
        var text2add = new TckLabel(targetLabel, {
                                    fontFamily: targetFontType, 
                                    fontSize: targetFontSize,
                                    name: targetName,
                                    container : contain,
                                    id : targetId
                                });
        return text2add;
    }
    
    function buildImageObject(target, contain){
        var source = target[0].firstChild;
        var imgHeight = source.height;
        var imgWidth = source.width;
        
        var item2add = new fabric.Image(source);
        item2add.set({
                        width:              imgWidth, 
                        height:             imgHeight, 
                        id:                 target.attr('id'), 
                        container:          contain,
                        lockScalingFlip:    true,
                        hasRotatingPoint:   false,
                        lockSkewingX:       true,
                        lockSkewingY:       true
                    });
        item2add.setControlsVisibility({
            mb: false,
            ml: false,
            mr: false,
            mt: false
        });
        item2add.on('scaling', scalingHandler);
        return item2add;
    }
    
    
    /*
     * function adding item on the canvas
     * @param {DO} target
     * @param {string} holder
     * @param {point} position
     * @param {string} type
     * @return {bool} 
     */
    function addItem2Canvas(target, holder='main', position={x:1,y:1}, type='text')
    {
        console.log('target', target);
        var contain = (holder=='main')?rectMain:rectControl;
        target[0].id = holder+'.'+target[0].name;
        console.log(target);
        if(itemsOnCanvas.findIndex(function(elm){return (elm.id == target[0].id);})!=-1){
            fadeInOut("Element can be added only once per container", 8000);
            return false;
        }
        var item2add;
        switch(type) {
            case 'text':
                console.log('case text');
                item2add = buildTextObject(target, contain);
                break;
            case 'image':
                console.log('case image');
                item2add = buildImageObject(target, contain);
                break;
            default:
                console.log('error creating object');
                return;
        }
        item2add.left = position.x;
        item2add.top = position.y;
        //add item and check if not intersecting and fully inside.
        //  if not, try a random position up to 1500 times.
        //  if still not possible, remove the item
        canvas.add(item2add);
        var counter =0;
        var minLeft = (holder==='main')? containStart:controlStart;
        var maxLeft = (holder==='main')? containWidth+containStart-item2add.width 
                                    : controlStart+pxCtrlWidth - item2add.width; 
        while(isOutOfHolder(item2add) || isIntersecting(item2add)){
            item2add.setLeft(fabric.util.getRandomInt(minLeft, maxLeft));
            item2add.setTop(fabric.util.getRandomInt(0, canvas.height));
            item2add.setCoords();
            counter +=1;
            if (counter>1500){
                //TODO handle case !2B tested!
                canvas.remove(item2add);
                fadeInOut("Please make room before adding !", 8000);
                return false;
            }
        }
        //animate the newly added item
        fabric.util.animateColor('#FFF700', '#eee', 600, {
            onChange: function(val) {
            item2add.set('backgroundColor', val);
            canvas.renderAll();
          },
          onComplete: function(){
              item2add.set('backgroundColor', '');
              canvas.setActiveObject(item2add);
            }
        });
        canvas.renderAll();
        itemsOnCanvas.push(item2add);
        console.log(itemsOnCanvas);
        return true;
    }
    /*
     * add mandatories item on document load
     */
    function checkStatePutEvent() {
        $('.addLabelButton.checked').each(function () {
            console.log('checked', $(this));
            addItem2Canvas($(this));
            if (rectControl){
                addItem2Canvas($(this), 'control');
            }
        });
        deselectHandler();
    }

    function butSetState(button){
        var index = getActiveStyle(button.attr("data-property")).indexOf(button.attr("data-value"));
        if(index>-1){
            button.addClass("true");
        }else{
            button.removeClass("true");
        }
    }
    
    function toggleButton(button){
        $(button).toggleClass("true");
    }
    
    function checkChange(inputItem, selectObject, oldProp){
        if (isOutOfHolder(selectObject) || isIntersecting(selectObject)){
            setActiveStyle($(inputItem).attr("data-property"),oldProp);
            fadeInOut("overlapping and 'out of bounds' forbidden", 5000);
            canvas.discardActiveObject();
            canvas.setActiveObject(selectObject);
        }
    }
    
    //for button interacting style
    function toggleAction(button){
        toggleButton(button);
        var value = $(button).attr("data-value");
        var actual = getActiveStyle($(button).attr("data-property"));
        var newStyle = ($(button).hasClass("true"))? 
            actual+' '+value : actual.replace(value,'');
        newStyle = newStyle.trim();
        setActiveStyle($(button).attr("data-property"),newStyle);
        checkChange(button,canvas.getActiveObject(),actual);
    }
    
    //for slider or select
    function optionChange(slider){
        var oldValue = getActiveProp($(slider).attr("data-property"));
        setActiveProp($(slider).attr("data-property"),slider.value);
        checkChange(slider, canvas.getActiveObject(), oldValue);
    }
    
    //initialize sliders and selects
    function optionSetState(input){
        $(input).val(getActiveProp($(input).attr("data-property")));
    }
    

    
    //canvas objects
        //events
    canvas.on('object:selected', selectHandler);
    canvas.on('object:moving', movingHandler);
    canvas.on('selection:cleared', deselectHandler);
    //canvas.on('mouse:over', mOverHandler);
    canvas.on('mouse:up', mUpHandler);
    //canvas.on('object:modified', modifiedHandler);
    
    var upperCanvas = canvas.getSelectionElement();
    $(upperCanvas).on('mouseout', outHandler);
    
        //event functions handling
        //overlap and canvas bounding
    //var marg = -2;
    function isOutOfHolder(target){
        var placed = target.container;
        return !target.isContainedWithinRect(placed.TL, placed.BR);    
    }
    
    function isIntersecting(target){
        var isIt = false;
        for (var ind in itemsOnCanvas){
                if (target!=itemsOnCanvas[ind] && target.intersectsWithObject(itemsOnCanvas[ind])){
                    isIt = true;
                    break;
                }
            }
        
        return isIt;
    }
    
    function modifiedHandler(event){
        console.log('modified', event.target.scaleX);
    }
    
    function scalingHandler(){
        
        var obj = canvas.getActiveObject();
        obj.setCoords();
        if (isOutOfHolder(obj) || isIntersecting(obj)){
            bgColor = true;
            obj.set({scaleX: goodSize, scaleY: goodSize, top: goodTop, left: goodLeft});
            
        }else{
            goodSize = obj.scaleX;
            goodTop = obj.getTop();
            goodLeft = obj.getLeft();
            bgColor = false;
        }
        setActiveProp('stroke',bgColor?'red':'');
        setActiveProp('strokeWidth', bgColor?1:0);
        
    }
    
    //globals for element moving on canvas
    var goodTop, goodLeft;
    //for scaling
    var goodSize;
    //var canvasObjectMoving = false;
 
//    function mOverHandler(){
//        var selected = canvas.getActiveObject();
//        if (selected){
//            goodTop = selected.getTop();
//            goodLeft = selected.getLeft();
//            //canvasObjectMoving = true;
//        };
//    }
    
    //bgColor used as flag for modification on item (turned true for warning)
    var bgColor = false;
    
    function mUpHandler(event){
        if (canvas.getActiveObject()){
            setActiveProp('stroke','');
            setActiveProp('strokeWidth',0);
            bgColor = false;
            //canvasObjectMoving = false;
        }
    }
    
    
    
    function movingHandler() {       
        var targ = canvas.getActiveObject();
        draggedDiv = targ.name;
        targ.set({
            left: Math.round(targ.left / grid) * grid,
            top: Math.round(targ.top / grid) * grid
        });
        var lastTop = targ.getTop(),
            lastLeft = targ.getLeft();
        targ.setCoords();        
//        console.log('bg '+bgColor);
//        console.log('isIt '+isIntersecting(targ));
//        if(bgColor&&!dragdrop&&!isIntersecting(targ)){
//            console.log("out!");
//            createDraggable(targ);
//            dragdrop = true;
//        }
        if(isIntersecting(targ)||isOutOfHolder(targ)){
                
                targ.setLeft(goodLeft);
                targ.setTop(goodTop);
                bgColor= true;
                
            }
        else{
            goodLeft = lastLeft;
            goodTop = lastTop;
            bgColor = false;
            //dragdrop = false;
        }
        
        setActiveProp('stroke',bgColor?'red':'');
        setActiveProp('strokeWidth', bgColor?1:0);
        
    };

            
    function selectHandler(event) {
        selected = event.target;
        goodTop = selected.getTop();
        goodLeft = selected.getLeft();
        //ok for uniform scaling, add ScaleY if not
        goodSize = selected.getScaleX();
        console.log(goodSize);
        $('#canControls').find("*").prop("disabled", false);
        var $name = selected.name;
        if($.contains($('#sf_fieldset_mandatory').get(0),$('[id="'+$name+'"]').get(0))){
            $('#object-remove').prop("disabled", true);
        }
        $('#canControls .btn-object-action').each(function(i){
            butSetState($(this));
        });
        $('#canControls .sldr-object-action,.slct-object-action').each(function(i){
            optionSetState($(this));
        });
    };
    

    function deselectHandler() {
        $('#canControls').find(":button").each(function(){
           $(this).removeClass("true"); 
        });
        $('#canControls').find("*").prop("disabled", true);
    }
    
            //functions changing prop
    function getActiveStyle(styleName, object) {
        object = object || canvas.getActiveObject();
        if (!object)
            return '';

        return (object.getSelectionStyles && object.isEditing)
                ? (object.getSelectionStyles()[styleName] || '')
                : (object[styleName] || '');
    }
    ;

    function setActiveStyle(styleName, value, object) {
        object = object || canvas.getActiveObject();
        if (!object)
            return;

        if (object.setSelectionStyles && object.isEditing) {
            var style = {};
            style[styleName] = value;
            object.setSelectionStyles(style);
            object.setCoords();
        } else {
            object.set(styleName, value);
        }

        object.setCoords();
        canvas.renderAll();
    }
    ;

    function getActiveProp(name) {
        var object = canvas.getActiveObject();
        if (!object)
            return '';

        return object[name] || '';
    }

    function setActiveProp(name, value) {
        var object = canvas.getActiveObject();
        if (!object)
            return;
        object.set(name, value).setCoords();
        canvas.renderAll();
    }   

    

    // js event & menus
    //check and put on template the mandatory elements, bind the others to an event
    $(document).on("load", checkStatePutEvent());
    
    function fadeInOut(message, d) {
        var flC = $('#flashCanvas');
        if (flC.css("opacity") > 0)
            flC.stop(true, true);
        $('#flashCanvas').text(message);
        $('#flashCanvas').fadeTo(100, 1);
        $('#flashCanvas').fadeTo(duration=d,0);
    };
    
    //button canvas interaction
    $('#object-remove').on('click',function(event){
        var activeObj = canvas.getActiveObject();
        // pass if object is mandatory
        // not necessary if the button stays disabled, keep for safety
        if ($.contains($('#sf_fieldset_mandatory').get(0),$('input[id="'+activeObj.name+'"]').get(0))){
            fadeInOut("mandatory element cannot be removed", 5000);
        }else{
           
            removeItemFromCanvas(activeObj);
             
        }
    });
    
    $('.btn-object-action').on('click', function(event){
        toggleAction(event.target);
    });
    
    //slider moved, selection changed (canvas)
    $('.sldr-object-action, .slct-object-action').on('input', function(event){
        optionChange(event.target);
    });
    
    
    //upper menu buttons
    //test print
    document.getElementById("print").onclick = function () {
        console.log("printing !!!");
    };
    
    //save ticket to base
    document.getElementById("serializer").onclick = function (event) {
        //myTck = JSON.stringify(canvas);
        //console.log(myTck);
        event.preventDefault();

        canvas.setBackgroundColor('');
        var myTck = canvas.toSVG({width:"100%", height:"100%"});
        console.log(canvas.toObject());
        console.log(myTck);
        //document.getElementById("print").disabled = false;
//        $('#flash').load(
//                $('#tckTemplate').attr('action'),
//                {   event_id: $('input[name=event_id]').val(), 
//                    datacustom: myTck, 
//                    ticketheight: $('input[name=ticketHeight]').val(), 
//                    ticketwidth: $('input[name=ticketWidth]').val()
//                }
//        );
        //$('#tckTemplate').submit();
       $('#dataSvg').val(canvas.toSVG({width:"100%", height:"100%"}));
        var transition = $('#transition').fadeIn('fast');
        $('<iframe src="' + $(this).parent().prop('href') + '" id="about" style="width: 400px; height: 600px;"></iframe>')
                .hide().appendTo('body');
        $('#about').fadeIn(50);
        canvas.setBackgroundColor('white');
        return false;
    };
    
    $('#transition .close').on('click', function(){
        $('#about').detach();
    });

    
    //back button
    document.getElementById("back").onclick = function () {
        document.location.href='/tck_dev.php/ticket/customizeMenu/action.html';
    };
    
//    $( window ).resize(function() {
//       canvas.renderAll();
//       console.log('rendering resize');
//    });
    
 
//function removGlobal(event, ui){
//    removeItemFromCanvas(ui.draggable);
//    ui.draggable.detach();
//}  
    
    //drag&drop
$( window ).on( "load", function() {
    //canvas.on('custom:drop', function(event){});
    
    var dragFromTabsOptions = { 
                        drag: function(event,ui){
                            ui.position.top = event.pageY - 10;
                            ui.position.left = event.pageX - 30;
                         },

                        refreshPositions: true,
                        cancel: false,
                        zIndex: 100,
                        revert: "invalid", 
                        helper: 'clone',
                        cursor: 'move',
                        scope: "fromTabs"
                    };
    var dragFromTemplateOptions = Object.assign({}, dragFromTabsOptions);
    dragFromTemplateOptions.scope = "fromCanvas";
    
    $('#sf_admin_form_tab_menu .draggable').draggable(dragFromTabsOptions);

    $('#tktCanvas').droppable({ 
                                tolerance: "pointer",
                                scope: "fromTabs",
                                drop: function(event, ui) {
                                    dropPt = {x: event.pageX-event.target.parentNode.offsetLeft - 30, 
                                              y: event.pageY-event.target.parentNode.offsetTop - 10 };
                                    var dropping = ui.draggable.clone(false);
                                    var dropZone = 'main';
                                    if(rectControl && dropPt.x >= rectControl.TL.x && dropPt.x <= rectControl.BR.x){
                                        dropZone = 'control';
                                        }
                                    dropping.attr('id',dropZone+'.'+dropping.attr('id'));
                                    dropping.draggable(dragFromTemplateOptions);
                                    dropping.draggable('option', 'appendTo', 'body');
                                    
                                    var addItemFlag=false;
                                    if (dropping.attr('type')=='image' || dropping.is("button")){
                                        addItemFlag = addItem2Canvas(dropping, dropZone, dropPt, 'image');
                                    }else{
                                        addItemFlag = addItem2Canvas(dropping, dropZone, dropPt, 'text');
                                    }
                                    if(addItemFlag)
                                        dropping.appendTo($('#inputOnTemplate'));
                              }
                            });
    
    $('#sf_admin_form_tab_menu').droppable({
                                                tolerance: "pointer",
                                                scope: "fromCanvas",
                                                drop: function(event, ui) {
                                                    removeItemFromCanvas(canvas.getActiveObject());
                                                    //ui.draggable.detach();
                                                    //canvasObjectMoving = false;
                                                }
                                        });
    
});

    function outHandler(e){
        var activeObj = canvas.getActiveObject();
        if (bgColor && activeObj){   
            if(targId = activeObj.id){
            e.type = "mousedown.draggable";
            $('#inputOnTemplate [id="'+targId+'"]').trigger(e);
            }
        }
    };                                        
                                   
 
//display
$(window).on('load', function(){
    $(".scrolled").css('height',$("#tabList").height());
    console.log($('#tckTemplate #name').val());
});
//add image tab

</script>
