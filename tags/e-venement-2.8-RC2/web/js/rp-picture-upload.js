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

    if ( LI == undefined )
      var LI = {};
          
    LI.rpFileUpload = function(img, input)
    {
      if ( location.hash == '#debug' )
        console.error('uploading image to: '+$(input).attr('data-post-url'));
      $.ajax({
        type: 'post',
        url: $(input).attr('data-post-url'),
        data: {
          id: $('[data-contact-id]').attr('data-contact-id'),
          image: img.replace(/^data:image\/.{3,9};base64,/, ''),
          type: img.replace(/^data:/,'').replace(/;base64,.*$/,'')
        },
        success: function(){
          console.log('Picture changed');
          $('.picture .current img').prop('src', img);
          $('.picture .current').show();
          $('.picture .webcam').addClass('small').find('.photobooth').remove();
        }
      });
    }
    
    $(document).ready(function(){
      // delete picture
      $('.picture .current a').click(function(){
        var picture = $(this).parent().find('img');
        $.get($(this).prop('href'), function(){
          $(picture).prop('src','').prop('alt','');
        });
        return false;
      });
      
      // file upload
      $('.picture input[type=file]').change(function(){
        var fread = new FileReader();
        if ( $(this).prop('files')[0].type.match('image.*') )
        {
          fread.onloadend = function(){ LI.rpFileUpload(fread.result, this); }
          fread.readAsDataURL($(this).prop('files')[0]);
        }
        $(this).val('');
        return false;
      });
      
      // cam capture
      LI.ifMediaCaptureSupported(function(){
        $('.picture .webcam .start').click(function(){
          var input = this;
          $('.picture .current').hide();
          $(this).closest('.webcam').removeClass('small').find('.live').photobooth().on('image', function(event, data){
            LI.rpFileUpload(data, input);
          });
          return false;
        });
      },function(){
        $('.picture .webcam').remove();
        console.log('Media capture is not supported or no media is available...');
      });
    });
