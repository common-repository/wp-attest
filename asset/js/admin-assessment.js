jQuery(document).ready(function(){

  //Leeson assessment scripts
  if (jQuery('#assessment_lesson').length > 0) {

    jQuery('#assessment_if_opened_text').hide();

    jQuery('#attest_lesson_assessment_type').change(function(){

      var assessment_type = jQuery('option:selected', this).val();
      if (assessment_type == 'points') {
        jQuery('#attest_lesson_assessment_number, #assessment_points_text').show();
        jQuery('#assessment_if_opened_text').hide();
      } else if (assessment_type == 'if_opened') {
        jQuery('#assessment_points_text, #attest_lesson_assessment_number').hide();
        jQuery('#assessment_if_opened_text').show();
      } else {
        jQuery('#assessment_points_text, #assessment_if_opened_text, #attest_lesson_assessment_number').hide();
      }
    });

    //Course assessment script
  } else if (jQuery('#assessment_course').length > 0) {

    //Define toggle functions
    function toggle_input(action) {
      if (action == 'point') {
        jQuery('#attest_course_assessment_points, #lesson_assessment_points').show();
        jQuery('#attest_course_assessment_opened, #lesson_assessment_count').hide();
      } else if (action == 'open') {
        jQuery('#attest_course_assessment_opened, #lesson_assessment_count').show();
        jQuery('#attest_course_assessment_points, #lesson_assessment_points').hide();
      }
    }

    function toggle_input_more(action) {
      if (action == 'point') {
        jQuery('#attest_course_assessment_points_more, #lesson_assessment_points_more').show();
        jQuery('#attest_course_assessment_opened_more, #lesson_assessment_count_more').hide();
      } else if (action == 'open') {
        jQuery('#attest_course_assessment_opened_more, #lesson_assessment_count_more').show();
        jQuery('#attest_course_assessment_points_more, #lesson_assessment_points_more').hide();
      }
    }

    function toggle_condition(type, action) {

      if (type == 'lesson') {
        toggle_input(action);
        var new_table = jQuery('#attest_course_assessment_new_table').val();
        if (new_table == '1') {
          jQuery('#attest_course_assessment_add_new').hide();
          jQuery('tr[id="course_assessment_table"]').show();
        } else if (new_table == '0') {
          jQuery('#attest_course_assessment_add_new').show();
          jQuery('tr[id="course_assessment_table"]').hide();
        }
        jQuery('#attest_course_assessment_action, #course_assessment_number, #attest_course_assessment_text').show();
      } else {
        jQuery('#attest_course_assessment_action, #course_assessment_number, #attest_course_assessment_points, #attest_course_assessment_opened, #attest_course_assessment_add_new, tr[id="course_assessment_table"], #attest_course_assessment_text').hide();
      }
    }


    //Toggle elements upon loading and slecting dropdowns
    jQuery('tr[id="course_assessment_table"]').hide();

    jQuery('#attest_course_assessment_add_new').click(function() {
      jQuery(this).hide();
      jQuery('tr[id="course_assessment_table"]').show();
      jQuery('#attest_course_assessment_new_table').val(1);
    });

    jQuery('#course_assessment_delete').click(function(){
      if (!confirm("Do you really want to delete the assessment?")){
        return false;
      } else {
        jQuery('tr[id="course_assessment_table"]').hide();
        jQuery('#attest_course_assessment_add_new').show();
        jQuery('#attest_course_assessment_new_table').val(0);
      }
    });

    var assessment_action = jQuery('option:selected', '#attest_course_assessment_action').val();
    toggle_input(assessment_action);

    jQuery('#attest_course_assessment_action').change(function(){

      var assessment_action = jQuery('option:selected', this).val();
      toggle_input(assessment_action);
      if (assessment_action == 'point') {
        jQuery('#attest_course_assessment_opened').val('');
      } else if (assessment_action == 'open') {
        jQuery('#attest_course_assessment_points').val('');
      }
    });

    var assessment_action = jQuery('option:selected', '#attest_course_assessment_action_more').val();
    toggle_input_more(assessment_action);

    jQuery('#attest_course_assessment_action_more').change(function(){

      var assessment_action = jQuery('option:selected', this).val();
      toggle_input_more(assessment_action);
      if (assessment_action == 'point') {
        jQuery('#attest_course_assessment_opened_more').val('');
      } else if (assessment_action == 'open') {
        jQuery('#attest_course_assessment_points_more').val('');
      }
    });

    var assessment_type = jQuery('option:selected', '#attest_course_assessment_type').val();
    var assessment_action = jQuery('option:selected', '#attest_course_assessment_action').val();
    toggle_condition(assessment_type, assessment_action);

    jQuery('#attest_course_assessment_type').change(function(){

      var assessment_type = jQuery('option:selected', this).val();
      var assessment_action = jQuery('option:selected', '#attest_course_assessment_action').val();
      toggle_condition(assessment_type, assessment_action);
    });
  }
});
