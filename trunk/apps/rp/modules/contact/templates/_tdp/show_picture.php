<span class="tdp-picture sf_admin_form_field_picture_id">
  <?php use_javascript('helper') ?>
  <?php use_javascript('photobooth') ?>
  <?php echo $contact->Picture->getRawValue()->render() ?>
  <input type="file" name="file" />
  
  <script type="text/javascript"><!--
    $(document).ready(function(){
      $('.tdp-picture input[type=file]').change(function(){
        var form = $('<form></form>')
          .append($(this).clone(true))
          //.append('<input type="text" name="id" value="<?php echo $contact->id ?>" />')
        ;
        console.error(form.html());
        var data = new FormData(form.get(0)[0]);
        console.error(data);
        /*
        data.append('id', <?php echo $contact->id ?>);
        console.error(data);
        data.append('file', $('.tdp-picture input[type=file]').prop('files')[0]);
        console.error(data);
        */
        $.ajax({
          url: '<?php echo url_for('contact/newPicture') ?>',
          data: form.serialize(),
          success: function(){
            alert('glop');
          }
        });
        return false;
      });
      
      $('.tdp-picture').photobooth().on('image', function(event, data){
        $.ajax({
          type: 'post',
          url: '<?php echo url_for('contact/newPicture') ?>',
          data: {
            id: <?php echo $contact->id ?>,
            image: data.replace('data:image/png;base64,', ''),
            type: data.replace(/^data:/,'').replace(/;base64,.*$/,'')
          },
          success: function(){
            $('.tdp-picture img').prop('src', data);
          }
        });
      });
      LI.ifMediaCaptureNotSupported(function(){
        console.log('Media capture not supported or no media available...');
        $('.tdp-picture .photobooth').remove();
      });
    });
  --></script>
</span>
