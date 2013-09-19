<?php use_javascript('form-list') ?>
<?php use_stylesheet('form-list') ?>
<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_workspaces_list">
  <div class="label ui-helper-clearfix">
    <label for="manifestation_workspaces"><?php echo __('Workspaces list') ?></label>
  </div>
  <div id="form_workspaces" class="sf_admin_form_list ajax">
    <script type="text/javascript">
      document.getElementById('form_workspaces').url   = '<?php echo url_for('gauge/batchEdit?id='.$form->getObject()->id) ?>';
      document.getElementById('form_workspaces').field = '.sf_admin_form_field_value';
      
      document.getElementById('form_workspaces').functions = [];
      document.getElementById('form_workspaces').functions.push(function(){
        $('#form_workspaces .gauge-transferts .ui-icon').unbind().click(function(){
          if ( $('#form_workspaces .gauge-transferts.active').length > 1 )
            $('#form_workspaces .gauge-transferts.active').toggleClass('active');
          $(this).closest('.gauge-transferts').toggleClass('active');
          
          // graphical stuff
          buf = $(this).html();
          $(this).html($(this).attr('title'));
          $(this).attr('title',buf);
        });
        
        // logical stuff
        $('#form_workspaces .sf_admin_list_td_Gauge input[name="gauge[value]"]').change(function(event){
          if ( $(this).closest('tr').find('.gauge-transferts.active').length == 1
            && $('#form_workspaces .gauge-transferts.active').length > 1 )
          {
            $(this).closest('tr').find('.gauge-transferts').removeClass('active');
            input = $('#form_workspaces .gauge-transferts.active').closest('tr').find('input[name="gauge[value]"][type=text]');
            val = parseInt(input.val(),10) - parseInt($(this).val(),10) + parseInt(this.defaultValue,10);
            val = val > 0 ? val : 0;
            input.val(val);
            input.change();
            
            // cf. web/js/form-list.js for the rest
          }
        });
      });
    </script>
  </div>
</div>
