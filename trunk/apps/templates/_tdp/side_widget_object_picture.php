<?php if ( !$object->getTable()->hasRelation('Picture') || $object->isNew() ) return; ?>
<div class="tdp-picture">
  <?php use_javascript('helper') ?>
  <?php use_javascript('photobooth') ?>
  <div class="current">
    <a href="<?php echo url_for('contact/delPicture?id='.$object->id) ?>" target="_blank">x</a>
    <?php if ( $object->picture_id ): ?>
      <?php echo $object->Picture->getRawValue()->render() ?>
    <?php else: ?>
      <img />
    <?php endif ?>
  </div>
  <div class="webcam"><button class="start"><?php echo image_tag('camera.png') ?></button></div>
  <input type="file" name="file" />
  
  <script type="text/javascript"><!--
    if ( LI == undefined )
      var LI = {};
    
    LI.rpFileUpload = function(img)
    {
      $.ajax({
        type: 'post',
        url: '<?php echo url_for('contact/newPicture') ?>',
        data: {
          id: <?php echo $object->id ?>,
          image: img.replace(/^data:image\/.{3,9};base64,/, ''),
          type: img.replace(/^data:/,'').replace(/;base64,.*$/,'')
        },
        success: function(){
          console.log('Picture changed');
          $('.tdp-picture img').prop('src', img);
        }
      });
    }
    
    $(document).ready(function(){
      $('.tdp-picture .current a').click(function(){
        var picture = $(this).parent().find('img');
        $.get($(this).prop('href'), function(){
          $(picture).prop('src','').prop('alt','');
        });
        return false;
      });
      $('.tdp-picture input[type=file]').change(function(){
        var fread = new FileReader();
        if ( $(this).prop('files')[0].type.match('image.*') )
        {
          fread.onloadend = function(){ LI.rpFileUpload(fread.result); }
          fread.readAsDataURL($(this).prop('files')[0]);
        }
        $(this).val('');
        return false;
      });
      
      LI.ifMediaCaptureSupported(function(){
        $('.tdp-picture .webcam .start').click(function(){
          $(this).closest('.webcam').photobooth().on('image', function(event, data){
            LI.rpFileUpload(data);
          });
          $(this).remove();
          return false;
        });
      },function(){
        $('.tdp-picture .webcam').remove();
        console.log('Media capture is not supported or no media is available...');
      });
    });
  --></script>
</div>
