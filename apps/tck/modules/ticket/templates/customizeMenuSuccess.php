<?php use_stylesheet('/css/content.css');  ?>
<?php use_stylesheet('/css/event.css'); ?>
<?php // use_stylesheet('/css/manifestation.css');  ?>
<?php use_stylesheet('/css/view.css'); ?>
<?php use_stylesheet('/css/customize/customize.css'); ?>

<?php use_javascript('/sfAdminThemejRollerPlugin/js/jquery-ui.custom.min.js'); ?>
<?php use_helper('CrossAppLink') ?>

<?php //var_dump($events->getRawValue())?>
<div id="sf_admin_container">
    <div id="sf_admin_content" class="ui-corner-all ui-widget-content">
    <div class="ui-widget ui-corner-all ui-widget-content">
        <div class="ui-widget-header ui-corner-all fg-toolbar">
            <h1 title="">Create a new template</h1>
        </div>
        <form action="customize" method="POST">
        <div class='sf_admin_batch_actions_choice'>
            <br>
            <h3>
                Select a type of template
            </h3>
            <select required id="tempType" name="tempType" class="selectCustom ui-selectmenu ui-widget ui-state-default ui-selectmenu-dropdown ui-selectmenu-status">
                <option value="">please choose</option>
                <option value="therm">thermal printed ticket</option>
                <option value="dmz">dematerialized ('A' series)</option>
            </select><br>
        </div>
        <br style="clear: both;"> 
        <div id="selectionText" class="sf_admin_batch_actions_choice">

            <h3>

            </h3>
            <select id="selecItem" name="selecItem" class="selectCustom ui-selectmenu ui-widget ui-state-default ui-selectmenu-dropdown ui-selectmenu-status" disabled>
                <option value=""></option>
            </select>
            <input id='selButton' name='selButton' value="ok" class="butCustom ui-button ui-state-default ui-corner-all" type="submit">
        </div>
        </form>
    </div>
    </div>
    <div id="more">
        <table>
    <div class="ui-widget ui-corner-all ui-widget-content">
        <div class="ui-widget-header ui-corner-all fg-toolbar">
            <h1 title="">List of existing templates</h1>
        </div>
        <table>
  <thead class="ui-widget-header">
    <tr>
      <th class="sf_admin_text sf_admin_list ui-state-default ui-th-column" hidden>Id</th>
      <th class="sf_admin_text sf_admin_list ui-state-default ui-th-column">Name</th>
      <th class="sf_admin_text sf_admin_list ui-state-default ui-th-column">Description</th>
      <th class="sf_admin_text sf_admin_list ui-state-default ui-th-column">Type</th>
      <th class="sf_admin_text sf_admin_list ui-state-default ui-th-column">Link</th>
      <th class="sf_admin_text sf_admin_list ui-state-default ui-th-column">Height</th>
      <th class="sf_admin_text sf_admin_list ui-state-default ui-th-column">Width</th>
      <th class="sf_admin_text sf_admin_list ui-state-default ui-th-column">Controller</th>
      <th class="sf_admin_text sf_admin_list ui-state-default ui-th-column">Created at</th>
      <th class="sf_admin_text sf_admin_list ui-state-default ui-th-column">Updated at</th>
      <th class="sf_admin_text sf_admin_list ui-state-default ui-th-column">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($customTemplates as $cTemp): ?>
    <tr>
      <td><?php //echo $jobeet_job->getId() ?></td>
      <td><?php //echo $jobeet_job->getCategoryId() ?></td>
      <td><?php //echo $jobeet_job->getType() ?></td>
<!-- more columns here -->
      <td><?php //echo $jobeet_job->getCreatedAt() ?></td>
      <td><?php //echo $jobeet_job->getUpdatedAt() ?></td>
    </tr>
    <?php endforeach ?>
  </tbody>
</table>
    </div>
            </table>

    </div>
</div>


<script>
    
    var data2link;
    
    $("#tempType").change(function () {
        $('#selectionText h3').text("precise your selection and validate with 'ok'");
        $('#selecItem option').remove();
        var tempType = $("#tempType");
        switch(tempType.val()){
            //Keep options in case of huge differences in prod for thermal and other tickets
            //  or if the same menu is called for badges, member cards, etc.
            //  If not switch/case van be removed
            case "therm":
                
            case "dmz":
                data2link = <?php echo json_encode($events->getRawValue()) ?>;
                break;
            default :
                data2link = null;
        }
        //if choice is made for the type, choose the event linked (in case of a ticket)
        // or any other data set in the above switch
        var selecItem = $('#selecItem');
        if(data2link!=null){
            selecItem.prop("disabled", false);
            $('#selButton').prop("disabled", false);
            selecItem.append(new Option("global template (no link)",0));
            var eventArray = new Array();
            for(item in data2link){
                selecItem.append(new Option(item,data2link[item]));
            }

        }else {
            $('#selectionText h3').text("Please choose above");
            selecItem.prop("disabled", true).attr("size", 1); 
        }    
        
    });
      
    
</script>