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
          $(this).closest('.gauge-transferts').toggleClass('active');
          
          // graphical stuff
          buf = $(this).html();
          $(this).html($(this).attr('title'));
          $(this).attr('title',buf);
        });
        
        // logical stuff
        $('#form_workspaces .sf_admin_list_td_Gauge #gauge_value').change(function(event){
          if ( $('#form_workspaces .gauge-transferts.active').length > 0 )
          {
            $('#form_workspaces .gauge-transferts.active').removeClass('active');
            diff = $(this).val() - this.defaultValue;
            elt = $(this).closest('tr').next().find('.sf_admin_list_td_Gauge #gauge_value');
            
            if ( elt.length > 0 )
            {
              elt.val(elt.val()-diff);
              elt.change();
            }
            else
            {
              $(this).val(this.defaultValue);
              $(this).change();
              alert("<?php echo __("Cannot proceede to transfert because there is no gauge below this one") ?>");
            }
          }
        });
      });
    </script>
  </div>
</div>
