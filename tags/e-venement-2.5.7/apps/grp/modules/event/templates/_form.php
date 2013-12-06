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
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php include_stylesheets_for_form($form) ?>
<?php include_javascripts_for_form($form) ?>
<?php use_stylesheet('/sfFormExtraPlugin/css/jquery.autocompleter.css') ?>
<?php use_javascript('/sfFormExtraPlugin/js/jquery.autocompleter.js') ?>
<?php use_javascript('grp-event') ?>
<?php use_helper('CrossAppLink') ?>

<div class="sf_admin_form">
    <div class="sf_admin_actions_block ui-widget">
      <?php include_partial('event/form_actions', array('event' => $event, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
    </div>
  
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  
  <div id="copy-paste"><?php echo __('Drag and drop elsewhere to paste selected content') ?></div>
  
  <table class="grp-entry">
    <tbody>
      <?php $i = 1 ?>
      <?php foreach ( $entry->getRaw('ContactEntries') as $ce ): ?>
      <tr class="contact-<?php echo $ce->id ?> <?php echo ++$i%2 == 0 ? 'pair' : 'impair' ?> <?php if ( !is_null($ce->transaction_id) ) echo 'transposed' ?> <?php if ( $ce->confirmed ) echo 'confirmed' ?>">
        <?php $j = 0 ?>
        <td class="contact <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>"><?php $f = new ContactEntryForm($ce) ?>
          <?php echo form_tag_for($f, '@contact_entry') ?>
          <?php include_partial('form_contact',array('f' => $f, 'ce' => $ce)) ?>
          </form>
        </td>
        <?php foreach ( $entry->ManifestationEntries as $me ): ?>
        <?php
          $entry_element = Doctrine::getTable('EntryElement')->fetchOneByContactManifestation($ce->id, $me->id);
          if ( !$entry_element )
          {
            $entry_element = new EntryElement;
            $entry_element->contact_entry_id = $ce->id;
            $entry_element->manifestation_entry_id = $me->id;
            $entry_element->save();
          }
        ?>
        <td class="manifestation-<?php echo $me->id ?> <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?> <?php echo $entry_element->second_choice ? 'second_choice' : '' ?> <?php echo $entry_element->accepted ? 'accepted' : '' ?>">
          <script type="text/javascript"><!--
            $(document).ready(function(){
              $('tr.contact-<?php echo $ce->id ?> td.manifestation-<?php echo $me->id ?>').attr('title',"<?php echo $ce->Professional->Contact.' - '.$ce->Professional?>\n<?php echo $me->Manifestation ?>");
            });
          --></script>
          <div class="EntryTickets">
            <?php $et = new EntryTickets; $et->EntryElement = $entry_element; ?>
            <?php include_partial('entry_tickets',array('form' => new EntryTicketsForm($et), 'entry_element' => $entry_element)) ?>
            <?php foreach ( $entry_element->EntryTickets as $et ): ?>
            <?php include_partial('entry_tickets',array('form' => new EntryTicketsForm($et), 'entry_element' => $entry_element)) ?>
            <?php endforeach ?>
          </div>
          <div style="clear: both"></div>
          <?php $f = new EntryElementForm($entry_element) ?>
          <?php echo form_tag_for($f,'@entry_element',array('class' => 'EntryElement')) ?>
          <?php echo $f->renderHiddenFields() ?>
            <?php echo $f['second_choice']->getWidget()->getLabel() ?>
            <p>
              <span title="<?php echo sfConfig::get('app_messages_dashed',__('Needed')) ?>"><?php echo $f['second_choice'] ?></span><span title="<?php echo __('Accepted') ?>"><?php echo $f['accepted'] ?></span><!--<input type="submit" name="submit" value="<?php echo __('Save',null,'sf_admin') ?>" />-->
              <input type="hidden" name="<?php echo $f['manifestation_entry_id']->renderName() ?>" value="<?php echo $me->id ?>" />
              <input type="hidden" name="<?php echo $f['contact_entry_id']->renderName() ?>" value="<?php echo $ce->id ?>" />
              <span class="translinked" title="<?php echo __('Related transactions, as in a cancellation case') ?>"><?php
                if ( $entry_element->EntryTickets->count() > 0 && $ce->Transaction && $ce->Transaction->Translinked->count() > 0 )
                foreach ( $ce->Transaction->Translinked as $tr )
                  echo '<a class="cancelling" href="'.cross_app_url_for('tck','ticket/pay?id='.$tr->id).'">#'.$tr->id.'</a> ';
              ?></span>
            </p>
          </form>
        </td>
        <?php endforeach ?>
        <td class="<?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?> ticketting"<?php if ( $ce->transaction_id ): ?> title="<?php echo __('Transaction #%%t%%',array('%%t%%' => $ce->transaction_id)); ?>"<?php endif ?>>
          <p class="count"><span class="total">0</span></p>
          <p class="transpose" title="<?php echo __('Transpose to ticketting') ?>"><a href="<?php echo url_for('contact_entry/transpose?id='.$ce->id) ?>">&gt;&gt;</a></p>
          <?php if ( $ce->transaction_id ): ?><p class="untranspose">
            <span class="translinked" title="<?php echo __('Related transactions, as in a cancellation case') ?>"><?php
              if ( $ce->Transaction->Translinked->count() > 0 )
                foreach ( $ce->Transaction->Translinked as $tr )
                  echo '<a class="cancelling" href="'.cross_app_url_for('tck','ticket/pay?id='.$tr->id).'">#'.$tr->id.'</a> ';
            ?></span>
            <a href="<?php echo url_for('contact_entry/untranspose?id='.$ce->id) ?>">&lt;&lt;</a>
          </p><?php endif ?>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>
    <thead>
      <?php $j = 0 ?>
      <tr class="impair">
        <td class="title <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>"><p class="min-width"><?php echo __('Contacts') ?> / <?php echo __('Event') ?></p></td>
        <?php $manifs = array() ?>
        <?php foreach ( $entry->ManifestationEntries as $me ): ?>
        <?php $manifs[] = $me->Manifestation->id ?>
        <td class="manifestation manifestation-<?php echo $me->id ?> <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>">
          <?php include_partial('form_manifestation',array('me' => $me, )) ?>
        </td>
        <?php endforeach ?>
        <td id="manifestation_entry_new" class="<?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>">
          <?php $f = new ManifestationEntryForm ?>
          <?php echo form_tag_for($f,'@manifestation_entry') ?>
            <?php echo $f->renderHiddenFields(); ?>
            <p><?php $f['manifestation_id']->getWidget()->setOption('query',$f['manifestation_id']->getWidget()->getOption('query')->andWhere('m.event_id = ?',$event->id)->andWhereNotIn('m.id',$manifs)); echo $f['manifestation_id']; ?></p>
            <p>
              <input type="submit" name="submit" value="<?php echo __('Save',array(),'sf_admin') ?>" />
            </p>
          </form>
        </td>
      </tr>
    </thead>
    <tfoot>
      <?php $j = 0 ?>
      <tr class="contact_entry_new <?php echo ++$i%2 == 0 ? 'pair' : 'impair' ?>">
        <td id="contact_entry_new" class="contact <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>">
          <?php $f = new ContactEntryForm ?>
          <?php echo form_tag_for($f,'@contact_entry') ?>
            <?php include_partial('form_contact_new',array('f' => $f)) ?>
          </form>
        </td>
        <?php foreach ( $entry->ManifestationEntries as $me ): ?>
        <td class="<?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?> count manifestation-<?php echo $me->id ?>">
        </td>
        <?php endforeach ?>
        <td class="<?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?> total"></td>
      </tr>
    </tfoot>
  </table>
  
  <script type="text/javascript">
    $(document).ready(function(){
      // if we submit any form
      $('form').submit(function(){
        $(this).find('input[name="contact_entry_new[entry_id]"],input[name="contact_entry[entry_id]"],input[name="manifestation_entry[entry_id]"]').val('<?php echo $entry->id ?>');
        form = this;
        $.post($(this).attr('action'),$(this).serialize(),function(data){
          if ( $(form).closest('#manifestation_entry_new, #contact_entry_new').length > 0 )
            window.location.reload();
          else
            $('#transition .close').click();
        });
        return false;
      });
      
      // if we suppress any contact or manifestation
      $('.delete').click(function(){
        if ( confirm('<?php echo __('Are you sure?',array(),'sf_admin') ?>') )
        {
          $.get($(this).attr('href'),function(){
            window.location.reload();
          });
          return false;
        }
      });
      
      // if we submit a cell
      $('form.EntryElement').unbind().submit(function(){
        var form = this;
        $.post($(this).attr('action'),$(this).serialize(),function(data){
          data = $.parseHTML(data);
          $('#transition .close').click();
          
          $(form).find('input[name="entry_element[second_choice]"]:checked').length > 0
            ? $(form).closest('td').addClass('second_choice')
            : $(form).closest('td').removeClass('second_choice');
          
          $(form).find('input[name="entry_element[accepted]"]:checked').length > 0
            ? $(form).closest('td').addClass('accepted')
            : $(form).closest('td').removeClass('accepted');
          
          $(form).html($(data).find('form').html());
          $(form).prepend('<p></p>');
          $(form).find('label').each(function(){
            $(form).find('p:first').prepend($('<span></span> ').attr('title',$(this).html()).prepend($(this).parent().find('input')));
          });
          //$(form).find('p:last').append($(form).find('.sf_admin_action_save input[type=submit]'));
          
          $(form).find('.content, .sf_admin_form_row, label, fieldset, .sf_admin_actions, input[name=_save_and_add]').remove();
          
          // autosubmit on ticking checkbox
          $(form).find('input[type=checkbox]').change(function(){
            $(this).closest('form').submit();
          });
        });
        
        grp_extra_empty_fields_cleanup();
        return false;
      });
      
      // if a line has already been transposed, we disable any action on it
      $('tbody tr.transposed td:not(.contact) input, tbody tr.transposed select').attr('disabled','disabled');
      $('tbody tr.transposed a.delete').remove();
      
      calculate_gauges(); // calculate how many tickets we've got
      change_tickets();   // auto submit tickets forms
      $('.EntryTickets form').unbind().submit(form_entry_tickets); // if we submit a tickets' form
      $('.EntryTickets a').unbind().click(a_entry_tickets); // if we submit a deletion on tickets lines
    });
    
    // autosubmit on ticking checkbox
    $('form.EntryElement input[type=checkbox]').change(function(){
      $(this).closest('form').submit();
    });
    
    function a_entry_tickets()
    {
      $.get($(this).attr('href'));
      $(this).closest('form').remove();
      calculate_gauges();
      return false;
    }
    
    function change_tickets()
    {
      $('.EntryTickets form input, .EntryTickets form select').unbind().change(function(){
        form = $(this).closest('form');
        if ( parseInt(form.find('input').val(),10) != 0 && form.find('select').val() != '' )
          form.submit();
      });
    }
    
    function form_entry_tickets()
    {
      var form = this;
      $.post($(this).attr('action'),$(this).serialize(),function(data){
        data = $.parseHTML(data);
        $('#transition .close').click();
        
        f = $(data).find('form');
        f.submit(form_entry_tickets);
        f.find('a').unbind().click(a_entry_tickets);
        f.prepend($('<p></p>').append(
          f.find('input[name="entry_tickets[quantity]"],select[name="entry_tickets[price_id]"],input[type=hidden],a.delete')
        ));
        f.find('.content, .sf_admin_form_row, label, fieldset, .sf_admin_actions, input[name=_save_and_add]').remove();
        
        // if new
        if ( $(form).find('a.delete').length == 0 )
        {
          $(form).parent().append(f);
          $(form).find('input[type=text]').val('').focus();
          $(form).find('select').val('');
        }
        else
          $(form).replaceWith(f);
        
        calculate_gauges();
        grp_extra_empty_fields_cleanup();
        change_tickets();   // auto submit tickets forms
      });
      return false;
    }
    
    function calculate_gauges()
    {
      if ( $('#no-calculate-gauge').length > 0 )
        return;
      
      $('tfoot .count > *').remove();
      $('.ticketting .count > *:not(.total)').remove();
      $('.ticketting .count .total').html(0);
      $('tfoot .count').each(function(){
        var curclass = /manifestation-\d+$/.exec($(this).attr('class'));
        
        $.get('<?php echo url_for('event/gauge') ?>?manifestation_id='+/\d+$/.exec(curclass),function(data){
          gauge = $($.parseHTML(data)).find('.gauge');
          $('.count.'+gauge.attr('id')).append(gauge);
        });
      });
      
      // line by line
      $('tbody tr input[name="entry_tickets[quantity]"]').each(function(){
        if ( $(this).closest('form').find('select').val() != '' )
        {
          if ( $(this).closest('tr').find('.ticketting .count .price-id-'+$(this).closest('form').find('select').val()).length > 0 )
          {
            nb = $(this).closest('tr').find('.ticketting .count .price-id-'+$(this).closest('form').find('select').val()+' .nb');
            nb.html(parseInt(nb.html(),10)+parseInt($(this).val(),10));
          }
          else
          {
            $(this).closest('tr').find('.ticketting .count')
              .prepend('<span class="tickets price-id-'+$(this).closest('form').find('select').val()+'"><span class="nb">'+$(this).val()+'</span><span class="name">'+$(this).closest('form').find('select option:selected').html()+'</span></span>');
          }
          
          // total calculation
          total = $(this).closest('tr').find('.ticketting .count .total');
          total.html(parseInt(total.html(),10)+parseInt($(this).val(),10));
        }
      });
    }
  </script>

</div>
