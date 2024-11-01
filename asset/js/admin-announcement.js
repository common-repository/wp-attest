jQuery(document).ready(function(){
  jQuery('#announcement_course_name_trigger').change(function() {
    var course = jQuery('option:selected', this).attr('data-name');
    jQuery('#announcement_course_name').text(course);
  });
});
