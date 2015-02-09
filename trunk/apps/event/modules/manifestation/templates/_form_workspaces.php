<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php use_javascript('form-list') ?>
<?php use_javascript('manifestation-gauge-grouping') ?>
<?php use_stylesheet('form-list') ?>
<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_workspaces_list">
  <div class="label ui-helper-clearfix">
    <label for="manifestation_workspaces"><?php echo __('Workspaces list') ?></label>
  </div>
  <div class="help">
    <span class="ui-icon ui-icon-help floatleft"></span>
    <?php echo __('Categories are usefull to group gauges for online sales. Use only if needed.') ?>
  </div>
  <div id="form_workspaces" class="sf_admin_form_list ajax">
    <script type="text/javascript">
      document.getElementById('form_workspaces').url   = '<?php echo url_for('gauge/batchEdit?id='.$form->getObject()->id) ?>';
      document.getElementById('form_workspaces').field = '.sf_admin_form_field_value';
      
      if ( LI.manifestationFormWorkspaces == undefined )
        LI.manifestationFormWorkspaces = [];
      LI.manifestationFormWorkspaces.push(function(){
        $('#form_workspaces .gauge-transferts .ui-icon').unbind().click(function(){
          if ( $('#form_workspaces .gauge-transferts.active').length > 1 )
            $('#form_workspaces .gauge-transferts.active').toggleClass('active');
          $(this).closest('.gauge-transferts').toggleClass('active');
          
          // graphical stuff
          var buf = $(this).html();
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
      
      if ( document.getElementById('form_workspaces').functions == undefined )
        document.getElementById('form_workspaces').functions = [];
      $.each(LI.manifestationFormWorkspaces, function(id, fct){
        document.getElementById('form_workspaces').functions.push(fct);
      });
    </script>
  </div>
</div>
