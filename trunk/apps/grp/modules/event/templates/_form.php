<?php include_stylesheets_for_form($form) ?>
<?php include_javascripts_for_form($form) ?>
<?php use_stylesheet('/sfFormExtraPlugin/css/jquery.autocompleter.css') ?>
<?php use_javascript('/sfFormExtraPlugin/js/jquery.autocompleter.js') ?>

<div class="sf_admin_form">
    <div class="sf_admin_actions_block ui-widget">
      <?php include_partial('event/form_actions', array('event' => $event, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
    </div>
  
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  
  <table style="border: 1px solid silver;">
    <tbody>
      <?php $i = 1 ?>
      <?php foreach ( $entry->getRaw('ContactEntries') as $ce ): ?>
      <tr class="contact-<?php echo $ce->id ?> <?php echo ++$i%2 == 0 ? 'pair' : 'impair' ?>">
        <?php $j = 0 ?>
        <td class="contact <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>"><?php $f = new ContactEntryForm($ce) ?>
          <?php echo form_tag_for($f, '@contact_entry') ?>
          <?php echo $f->renderHiddenFields() ?>
          <p>
            <a href="<?php echo cross_app_url_for('rp','contact/show?id='.$ce->Professional->Contact->id) ?>"><?php echo $ce->Professional->Contact ?></a>
            -
            <a href="<?php echo cross_app_url_for('rp','organism/show?id='.$ce->Professional->Organism->id) ?>"><?php echo $ce->Professional ?></a>
            <input type="hidden" name="<?php echo $f['professional_id']->getName() ?>" value="<?php echo $f['professional_id']->getValue() ?>" />
          </p>
          <p title="<?php echo __('Note') ?>"><?php echo $f['comment1'] ?></p>
          <p title="<?php echo __('Confirmation') ?>"><?php echo $f['comment2'] ?></p>
          <p title="<?php echo __('Confirmed') ?>"><?php echo $f['confirmed'] ?></p>
          <p class="sf_admin_actions">
            <?php echo link_to(__('Delete',array(),'sf_admin'), 'contact_entry/del?id='.$ce->id, array('class' => 'delete')); ?>
            <input type="submit" value="<?php echo __('Save',array(),'sf_admin') ?>" />
          </p>
          </form>
        </td>
        <?php foreach ( $entry->ManifestationEntries as $me ): ?>
        <td class="manifestation-<?php echo $me->id ?> <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>">
          <?php
            $entry_element = Doctrine::getTable('EntryElement')->fetchOneByContactManifestation($ce->id, $me->id);
            if ( !$entry_element )
            {
              $entry_element = new EntryElement;
              $entry_element->entry_id = $entry->id;
              $entry_element->contact_entry_id = $ce->id;
              $entry_element->manifestation_entry_id = $me->id;
              $entry_element->save();
            }
          ?>
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
              <span title="<?php echo __('Second choice') ?>"><?php echo $f['second_choice'] ?></span><span title="<?php echo __('Accepted') ?>"><?php echo $f['accepted'] ?></span><input type="submit" name="submit" value="<?php echo __('Save',null,'sf_admin') ?>" />
              <input type="hidden" name="<?php echo $f['entry_id']->renderName() ?>" value="<?php echo $entry->id ?>" />
              <input type="hidden" name="<?php echo $f['manifestation_entry_id']->renderName() ?>" value="<?php echo $me->id ?>" />
              <input type="hidden" name="<?php echo $f['contact_entry_id']->renderName() ?>" value="<?php echo $ce->id ?>" />
            </p>
          </form>
        </td>
        <?php endforeach ?>
        <td class="<?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?> ticketting"<?php if ( $ce->transaction_id ): ?> title="<?php echo __('Transaction #%%t%%',array('%%t%%' => $ce->transaction_id)); ?>"<?php endif ?>>
          <p class="transpose"><a href="<?php echo url_for('contact_entry/transpose?id='.$ce->id) ?>">&gt;&gt;</a></p>
          <?php if ( $ce->transaction_id ): ?><p class="untranspose"><a href="<?php echo url_for('contact_entry/untranspose?id='.$ce->id) ?>">&lt;&lt;</a></p><?php endif ?>
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
          <a href="<?php echo cross_app_url_for('event','event/show?id='.$me->Manifestation->Event->id) ?>"><?php echo $me->Manifestation->Event ?></a>
          @
          <a href="<?php echo cross_app_url_for('event','manifestation/show?id='.$me->Manifestation->id) ?>">
            <?php echo format_date($me->Manifestation->happens_at,'EEE, dd MMM yyyy') ?>
          </a>
          -
          <?php echo link_to(__('Delete',array(),'sf_admin'), 'manifestation_entry/del?id='.$me->id, array('class' => 'delete')); ?>
        </td>
        <?php endforeach ?>
        <td id="manifestation_entry_new" class="<?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>">
          <?php $f = new ManifestationEntryForm ?>
          <?php echo form_tag_for($f,'@manifestation_entry') ?>
            <?php echo $f->renderHiddenFields(); ?>
            <p><?php $f['manifestation_id']->getWidget()->setOption('query',Doctrine::getTable('Manifestation')->createQuery('m')->andWhere('m.event_id = ?',$event->id)->andWhereNotIn('m.id',$manifs)); echo $f['manifestation_id']; ?></p>
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
            <?php echo $f->renderHiddenFields(); ?>
            <p title="<?php echo __('Contact') ?>"><?php echo $f['professional_id'] ?></p>
            <p title="<?php echo __('Note') ?>"><?php echo $f['comment1'] ?></p>
            <p title="<?php echo __('Confirmation') ?>"><?php echo $f['comment2'] ?></p>
            <p title="<?php echo __('Confirmed') ?>"><?php echo $f['confirmed'] ?></p>
            <p class="sf_admin_actions">
              <input type="submit" value="<?php echo __('Save',array(),'sf_admin') ?>" />
            </p>
          </form>
        </td>
        <?php foreach ( $entry->ManifestationEntries as $me ): ?>
        <td class="<?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>"></td>
        <?php endforeach ?>
        <td class="<?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>"></td>
      </tr>
    </tfoot>
  </table>
  
  <script type="text/javascript">
    $(document).ready(function(){
      $('form').submit(function(){
        $(this).find('input[name="contact_entry[entry_id]"],input[name="manifestation_entry[entry_id]"]').val('<?php echo $entry->id ?>');
        $.post($(this).attr('action'),$(this).serialize(),function(data){
          window.location.reload();
        });
        return false;
      });
      $('.delete').click(function(){
        if ( confirm('<?php echo __('Are you sure?') ?>') )
        {
          $.get($(this).attr('href'),function(){
            window.location.reload();
          });
          return false;
        }
      });
      
      $('form.EntryElement').unbind().submit(function(){
        var form = this;
        $.post($(this).attr('action'),$(this).serialize(),function(data){
          $('#transition .close').click();
          $(form).html($(data).find('form').html());
          $(form).prepend('<p></p>');
          $(form).find('label').each(function(){
            $(form).find('p:first').prepend($('<span></span> ').attr('title',$(this).html()).prepend($(this).parent().find('input')));
          });
          $(form).find('p:last').append($(form).find('.sf_admin_action_save input[type=submit]'));
          
          $(form).find('.content, .sf_admin_form_row, label, fieldset, .sf_admin_actions').remove();
        });
        return false;
      });

      $('.EntryTickets form').unbind().submit(form_entry_tickets);
      $('.EntryTickets a').unbind().click(a_entry_tickets);
    });
    
    function a_entry_tickets()
    {
      $.get($(this).attr('href'));
      $(this).closest('form').remove();
      return false;
    }
    
    function form_entry_tickets()
    {
      var form = this;
      $.post($(this).attr('action'),$(this).serialize(),function(data){
        $('#transition .close').click();
        
        f = $(data).find('form');
        f.submit(form_entry_tickets);
        f.find('a').unbind().click(a_entry_tickets);
        f.prepend($('<p></p>').append(
          f.find('input[name="entry_tickets[quantity]"],select[name="entry_tickets[price_id]"],input[type=hidden],input[type=submit],a.delete')
        ));
        f.find('.content, .sf_admin_form_row, label, fieldset, .sf_admin_actions').remove();
        
        // if new
        if ( f.find('input[name="entry_tickets[id]"]').val() != '' )
        {
          $(form).parent().append(f);
          $(form).find('input[type=text]').val('').focus();
          $(form).find('select').val('');
        }
        else
          $(form).replaceWith(f);
      });
      return false;
    }
  </script>

</div>
