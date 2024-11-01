jQuery(document).ready(function(){

  //Settings tabs
  jQuery('#tab-container').easytabs(
    {animate: false}
  );
  jQuery('#details-tab-container').easytabs(
    {animate: false}
  );

  //Students section
  function toggle_students(enrolled, element) {
    if(enrolled == 'auto') {
      jQuery(element).hide();
    } else if (enrolled == 'define') {
      jQuery(element).show();
    }
  }

  jQuery('#attest_course_students_enrolled_number, #attest_course_students_to_enroll_number, #attest_course_students_excess_error_conatiner').hide();

  var enrolled = jQuery('input[name="attest_course_students[enrolled]"]:checked').val();
  toggle_students(enrolled, '#attest_course_students_enrolled_number');

  var to_enroll = jQuery('input[name="attest_course_students[to_enroll]"]:checked').val();
  toggle_students(to_enroll, '#attest_course_students_to_enroll_number, #attest_course_students_excess_error_conatiner');

  jQuery('.attest_course_students_enrolled').click( function() {
    var enrolled = jQuery('input[name="attest_course_students[enrolled]"]:checked').val();
    toggle_students(enrolled, '#attest_course_students_enrolled_number');
  });

  jQuery('.attest_course_students_to_enroll').click( function() {
    var to_enroll = jQuery('input[name="attest_course_students[to_enroll]"]:checked').val();
    toggle_students(to_enroll, '#attest_course_students_to_enroll_number, #attest_course_students_excess_error_conatiner');
  });


  /*Toggle price*/
  function toggle_price(price_type) {
    if (price_type == 'paid') {
      jQuery('#attest_price_value').show();
    } else if (price_type == 'free') {
      jQuery('#attest_price_value').hide();
    }
  }

  var price_type = jQuery('input[name="attest_course_price"]:checked').val();
  toggle_price(price_type);

  jQuery('input[name="attest_course_price"]').change(function() {
    var type = jQuery(this, ':checked').val();
    toggle_price(type);
  });
});
