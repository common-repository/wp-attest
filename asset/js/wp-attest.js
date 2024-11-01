jQuery(document).ready(function(){

  jQuery('#attest_password_match_text_register').hide();
  jQuery('#attest_password_confirm_register').change(function() {
    var password = jQuery('#attest_password_register').val();
    var confirmPassword = jQuery(this).val();
    if (password != confirmPassword) {
      jQuery('#attest_password_match_text_register').show();
      //jQuery('#attest_submit_register').attr('disabled', 'disabled');
    } else {
      jQuery('#attest_password_match_text_register').hide();
      //jQuery('#attest_submit_register').removeAttr('disabled');
    }
  });
/**
  var max_modal_limit = jQuery('#attest_total_number_of_sections').val();
  var video = [];
  for (var i = 1; i < max_modal_limit; i++) {
    video.push({'id':i, 'elem':jQuery('#attestIntroVideoModal_'+i).find('#modal_body_'+i).html()});
  }

  jQuery('.bd-example-modal-lg').on('shown.bs.modal', function() {
    jQuery(video).each(function(index, item) {
      if (undefined !== jQuery(this).find('#modal_body_'+item.id)) {
console.log(jQuery(this).find('#modal_body_'+item.id));
console.log(item.elem);
        jQuery(this).find('#modal_body_'+item.id).html(item.elem);
      }
    });
  });
*/
});
