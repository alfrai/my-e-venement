<?php
//$mandat = $sf_data->getRaw('mandatory');
//$ticket = $sf_data->getRaw('ticket');
//$manif = $sf_data->getRaw('manifestation');
//$gauge = $sf_data->getRaw('gauge');
//$transaction = $sf_data->getRaw('transaction');
//$others = $sf_data->getRaw('others');
$menu = $sf_data->getRaw('menu');
?>
<ul id="tabList" class = "ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header" role = "tablist">
<?php 
    $idCount = 1;
    foreach (array_keys($menu) as $key) {
        $tabIndex = -1;
        $ariaSelec = 'false';
        $classTab = "ui-state-default ui-corner-all";
        if($key=='mandatory'){
            $tabIndex = 0;
            $ariaSelec = 'true';
            $classTab.= ' ui-tabs-active ui-state-active';
        }
        echo('<li class = "'.$classTab.'" role = "tab" tabindex = "'.$tabIndex.'" aria-controls = "sf_fieldset_'.$key.'" aria-labelledby = "ui-id-'.$idCount.'" aria-selected = "'.$ariaSelec.'">
        <a href = "#sf_fieldset_'.$key.'" class = "ui-tabs-anchor" role = "presentation" tabindex = "-1" id = "ui-id-'.$idCount.'">'.ucfirst($key).'</a>
    </li>');
        $idCount+=1;
    }
    
    
    echo('<li class = "ui-state-default ui-corner-all" role = "tab" tabindex = "'.$tabIndex.'" aria-controls = "sf_fieldset_images" aria-labelledby = "ui-id-'.$tabIndex.'" aria-selected = "false"><a href = "#sf_fieldset_images" class = "ui-tabs-anchor" role = "presentation" tabindex = "-1" id = "ui-id-'.$tabIndex.'">Images</a></li>');                
?>
</ul>
<?php
$idCount = 1;
foreach ($menu as $fieldset => $elements) {
        $className = "addLabelButton draggable";
        $disabled ='';
        if($fieldset=='mandatory'){
            $className="addLabelButton checked";
            $disabled = 'disabled="disabled"';
        }
    echo('<div id="sf_fieldset_'.$fieldset.'" class="scrolled ui-corner-all ui-tabs-panel ui-widget-content ui-corner-bottom" aria-labelledby="ui-id-'.$idCount.'" role="tabpanel" aria-expanded="true" aria-hidden="false">');
        foreach ($elements as $value) {
            
            echo '<input id="'.$value.'" class="'.$className.'" type="button" value="'.$value.'"  name="'.$value.'"'.$disabled.'>';
            
        }
    echo('</div>');
    $idCount+=1;
}

echo('<div id="sf_fieldset_images" class="scrolled ui-corner-all ui-tabs-panel ui-widget-content ui-corner-bottom" aria-labelledby="ui-id-'.$idCount.'" role="tabpanel" aria-expanded="false" aria-hidden="true">'
        . '<button type="button" id="barcode" class="addLabelButton draggable"><img src="/images/customize/BCodeSample.png" height="42" width="115" alt="BarCode"></button>'
        . '<button type="button" id="qrcode" class="addLabelButton draggable"><img src="/images/customize/QrCodeSample.png" height="50" width="50" alt="QrCode"></button>'
        . '</div>');

?>


