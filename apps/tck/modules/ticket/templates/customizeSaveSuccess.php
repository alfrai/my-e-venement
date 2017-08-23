<?php use_javascript('menu') ?>
<?php include_partial('global/assets') ?>
<div id="sf_fieldset_general" class="ui-corner-all ui-tabs-panel ui-widget-content ui-corner-bottom" style="display: block;">
    <div class="fg-toolbar ui-widget-header ui-corner-all">
        <h1>New template</h1>
    </div>
    <form>
    <div class="sf_admin_form_row sf_admin_text sf_admin_form_field_short_name">
        <label for="name">Nom</label>
        <div class="label ui-helper-clearfix"></div>
        <div class="widget" title="Name" >
            <input name="name" id="name" type="text" placeholder="name is mandatory" maxlength="80" required>
        </div>
    </div>
                                  
    
    <div class="sf_admin_form_row sf_admin_textarea">
        <label for="description">Description</label>
        <div class="label ui-helper-clearfix"></div>
        <div class="widget" title="Description">
            <textarea name="description" id="description" cols="40" rows="5" placeholder="You can also provide a short description" maxlength="255"></textarea>
        </div>
    </div>

        <div class="widget ">
            <select id="event_id" name="event_id" class="selectCustom ui-selectmenu ui-widget ui-state-default ui-selectmenu-dropdown ui-selectmenu-status">
                <option value="0">global template (no link)</option>
                <?php foreach ($sf_data->getRaw('events') as $key => $value) {
                    
                    echo('<option value="'.$value.'">'.$key.'</option>');
                }?>
            </select>

        </div>
      
    
        <div>
            <button class="fg-button ui-state-default fg-button-icon-left" id="save" type="button"><span class="ui-icon ui-icon-circle-check"></span>confirm saving</button>
            <button class="fg-button ui-state-default fg-button-icon-left" id="cancel" type="button"><span class="ui-icon ui-icon-cancel"></span>cancel</button>
        </div>
    </form>
</div> 

<script>
    var saving = function (){
        var formTemp = $('#tckTemplate', window.parent.document);
        $('#flash', window.parent.document).load(formTemp.attr('action'),{
                event_id: $('input[name=event_id]').val(),
                name: $('input[name=name]').val(),
                description: $('input[name=description').val()
                }
            );
        //$('#tckTemplate').submit();
        $('cancel').trigger('click');
    };
    //console.log('name', $('#tckTemplate #name', window.parent.document).val());
    var defaultEvent = $('#tckTemplate #event_id', window.parent.document).val();
    $('#event_id option[value="'+defaultEvent+'"]').prop('selected', true);
    $('#cancel').on('click', function(){$('#transition .close', window.parent.document).trigger("click");});
    $('#save').on('click', saving);

</script>


