jQuery(document).ready( function( jQuery ) {

  function toggle_select(select) {

    var type = jQuery('option:selected', select).val();

    if (type == 'upload') {
      jQuery('#attest_intro_video_embed').hide();
      jQuery('#attest_intro_video_text, #attest_intro_video_upload, #attest_intro_video_url').show();
    } else if (type == 'embed') {
      jQuery('#attest_intro_video_upload, #attest_intro_video_url, #attest_intro_video_wistia').hide();
      jQuery('#attest_intro_video_text, #attest_intro_video_embed').show();
    } else if (type == 'wistia_code') {
      jQuery('#attest_intro_video_upload, #attest_intro_video_url, #attest_intro_video_embed').hide();
      jQuery('#attest_intro_video_text, #attest_intro_video_wistia').show();
    } else if (type == 'none') {
      jQuery('#attest_intro_video_text, #attest_intro_video_upload, #attest_intro_video_url, #attest_intro_video_embed, #attest_intro_video_wistia').hide();
      jQuery('#attest_intro_video_url, #attest_intro_video_embed, #attest_intro_video_wistia').val('');
    } else {
      jQuery('#attest_intro_video_upload, #attest_intro_video_embed, #attest_intro_video_wistia').hide();
      jQuery('#attest_intro_video_text, #attest_intro_video_url').show();
    }
  }

  var select = jQuery('#attest_intro_video_type');
  toggle_select(select);

  jQuery('#attest_intro_video_type').change(function(){

    toggle_select(this);
  });

  jQuery('#attest_intro_video_upload').click(function() {

    var custom_uploader = wp.media({
      title: video_modal.title,
      library: {type: 'video/MP4'},
      multiple: false,
      button: {text: video_modal.button},
      multiple: false
    })
    .on('select', function() {
      var attachment = custom_uploader.state().get('selection').first().toJSON();
      jQuery('#attest_intro_video_url').val(attachment.url);
    })
    .open();
  });
});
