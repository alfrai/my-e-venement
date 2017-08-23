<?php decorate_with(false) ?>

<!--<div id="mySVG">
    <object type="application/pdf">-->
<?php 
    foreach ($tickets as $ticket) {

        var_dump($ticket);
}
    echo $svg;
//    $option = array('width'=>'150mm', 'heigth'=>'50mm');
//    $generator = new liPDFPlugin;
//    $generator->setOption('grayscale', true);
//    $generator->setOption('page-width', $options['width']);
//    $generator->setOption('page-size', 'Custom');
//    $sf_response->setContentType('application/pdf');
//    $generator->setOption('page-height', $options['height']);
//    foreach ( array('bottom', 'left', 'right', 'top') as $prop )
//        $generator->setOption('margin-'.$prop, '0');
//    $generator->setHtml($svg);
//    echo $generator->getPdf();
?>
<!--</div>
</object>-->
<!--<script src="/js/jquery.js"></script>
<script type="text/javascript" src="/js/print-tickets.js"></script>
<script>
    var printSVG = function()
    {
        var popUpAndPrint = function()
        {
            var container = $('#mySVG');
            console.log(container);
            var width = parseFloat($('svg').attr("width"));
            var height = parseFloat($('svg').attr("height"));
            console.log(width + ' '+ height);
            var printWindow = window.open('', 'PrintMap',
            'width=' + width + ',height=' + height);
            printWindow.document.writeln(container.html());
            printWindow.document.close();
            printWindow.print();
            printWindow.close();
        };
        setTimeout(popUpAndPrint, 500);
    };
    printSVG();
</script>

<script>
    var patt = /{{(.*?)}}/;
            
    var logos = document.getElementsByClassName("logo");
    console.log(logos);
    for (logo in logos){
        console.log(logo);
        logo.innerHTML='MONLOGO';
    }

    var gList = (document.getElementsByTagName("g"));



    for (var i=0; i<gList.length; i++){
        var id = gList.item(i).attr("id");
        var tspan = gList.item(i).getElementsByTagName('tspan').item(0);
        var value = 
        tspan.text("")
        
        var keys = (patt.exec(gList.item(i).getElementsByTagName('tspan').item(0).nodeValue))[1].split('.');
        var valueTkt = findProp(ticket, keys, 'error');
        attList[i].childNodes[0].nodeValue = valueTkt;
        console.log(keys, valueTkt);

    }; 
</script>-->
    