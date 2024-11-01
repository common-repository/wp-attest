/**
 *
 * Sections JS file
 *
 */
jQuery(document).ready(function() {

  //Toggle selector function
  function toggle_new_lesson_form(type, selector, form) {

    if (type == 'attest_lesson') {
      jQuery(form).find('#add_new_quizz').hide();
      jQuery(form).find('#add_new_quizz_exist').hide();

      if (selector == 'new') {
        jQuery(form).find('#add_new_lesson').show();
        jQuery(form).find('#add_new_lesson_exist').hide();
      } else if (selector == 'exist') {
        jQuery(form).find('#add_new_lesson').hide();
        jQuery(form).find('#add_new_lesson_exist').show();
      }
    }
  }

  //Toggle selector function
  function toggle_new_quizz_form(type, selector, form) {

    if (type == 'attest_quizz') {
      jQuery(form).find('#add_new_lesson').hide();
      jQuery(form).find('#add_new_lesson_exist').hide();

      if (selector == 'new') {
        jQuery(form).find('#add_new_quizz').show();
        jQuery(form).find('#add_new_quizz_exist').hide();
      } else if (selector == 'exist') {
        jQuery(form).find('#add_new_quizz').hide();
        jQuery(form).find('#add_new_quizz_exist').show();
      }
    }
  }

  //Global function
  function get_attest_curriculum(element) {

    var containers = jQuery(element).closest('#section_append_to').children('#section_container');
    var titles = jQuery(element).closest('#section_append_to').find("input[id='attest_curriculum_title']")
    .map(function(){return jQuery(this).val();}).get();

    var data_obj = [];
    containers.each(function(i, select_container){
      var lessons_obj = jQuery(select_container).find("select[id='attest_lesson_id']")
      .map(function(){return jQuery('option:selected', this).val();}).get();
      var conditions_obj = jQuery(select_container).find("select[id='attest_lesson_teaser']")
      .map(function(){return jQuery('selected', this).val();}).get();

      var output_obj = [];
      jQuery(lessons_obj).each(function(item, lesson){
        var arr = [];
        arr.push({'lesson_id': lesson});
        arr.push({'lesson_teaser': conditions_obj[item]});
        output_obj.push(arr);
      });
      data_obj[i] = output_obj;
    });

    var final_obj = [];
    jQuery(titles).each(function(item, value) {
      var arr = [];
      arr.push({'title': value});
      arr.push({item: data_obj[item]});
      final_obj[item] = arr;
    });

    return final_obj;
  }


  //Init
  jQuery('#attest_curriculum_title, #save_title').hide();


  //Add new section
  jQuery(document).delegate('.new_section', 'click', function(){

    var section_html = jQuery('#appendFrom').html();

    var serial_arr = [];
    jQuery(this).parent().parent().parent().children('#section_append_to').children('#section_container').each(function(i, v) {
      serial_arr.push(parseInt(jQuery(v).children('#section').children('#section_ID').attr('data-serial')));
    });
    var sorted_serial_arr = serial_arr.sort((a,b) => a-b);
    var section_Serial = sorted_serial_arr[sorted_serial_arr.length-1];

    var title = jQuery(this).closest('#new_section_form').find('#add_new_section').val();

    jQuery('#add_new_section').val('');

    if (section_Serial == undefined) {
      var section_count = 0;
    } else {
      var section_count = parseInt(section_Serial) + 1;
    }

    var altered_section_html = jQuery(section_html).find('#section_ID').attr('data-serial', section_count).end()
    .find('#attest_curriculum_title').attr('name', 'attest_curriculum['+section_count+'][title]').val(title).end()
    .find('#lesson_container').remove().end()
    .find('#accordion_title_number_section').text(section_count+1).end()
    .find('#accordion_title_name_section').text(title).end()
    .find('#attest_curriculum_title').text(title).end();

    jQuery(this).parent().parent().prev('#section_append_to').append(altered_section_html);

    jQuery('#new_section_link').show();
    jQuery('#new_section_form').hide();
  });


  //Delete section
  jQuery(document).delegate('.delete_section', 'click', function(){
    if (!confirm(curriculum_ajax.delete_section)){
      return false;
    } else {
      var count_lessons_obj = jQuery(this).closest('#section_append_to').find('#lesson_container');

      jQuery(this).parent().parent().parent().parent().parent().remove();

      var sections = jQuery('span[id="accordion_title_number_section"]');
      sections.each(function(i,v){
        if (i < sections.length) {
          jQuery(v).text(i+1);
        }
      });
    }
  });


  //Add new lesson
  jQuery(document).delegate('.new_lesson', 'click', function(){

    var element = this;
    var title = jQuery(this).parent().find('#add_new_lesson').val();
    var quizz_title = jQuery(this).parent().find('#add_new_quizz').val();

    var type = jQuery(this).parent().find('#choose_quizz_or_lesson option:selected').val();
    if (type == undefined) {
      type = 'attest_lesson';
    }

    var selector = jQuery(this).parent().find('#new_lesson_or_quizz_type_selector option:selected').val();

    var quizz_or_lesson = jQuery(this).parent().find('#add_new_quizz_or_lesson option:selected').val();

    var chosen_lesson_id = jQuery(this).parent().find('#add_new_lesson_exist option:selected').val();
    var chosen_lesson_url = jQuery(this).parent().find('#add_new_lesson_exist option:selected').attr('data-link');
    var chosen_lesson_edit_url = jQuery(this).parent().find('#add_new_lesson_exist option:selected').attr('data-edit-link');
    var chosen_lesson_title = jQuery(this).parent().find('#add_new_lesson_exist option:selected').attr('data-title');

    var chosen_quizz_id = jQuery(this).parent().find('#add_new_quizz_exist option:selected').val();
    var chosen_quizz_url = jQuery(this).parent().find('#add_new_quizz_exist option:selected').attr('data-link');
    var chosen_quizz_edit_url = jQuery(this).parent().find('#add_new_quizz_exist option:selected').attr('data-edit-link');
    var chosen_quizz_title = jQuery(this).parent().find('#add_new_quizz_exist option:selected').attr('data-title');

    var course_id = jQuery(this).parent().find('#attest_course_id').val();
    var nonce = jQuery(this).parent().find('#attest_new_lesson_or_quizz_nonce').val();

    if ( selector == 'exist' && ((chosen_lesson_id != undefined && chosen_lesson_id != '') || (chosen_quizz_id != undefined && chosen_quizz_id != ''))) {

      if (type == 'attest_lesson') {
        var lesson_html = jQuery('#appendFrom').children('#section_container').children('#section').children('#lesson_append_to').html();
      } else if (type == 'attest_quizz') {
        var lesson_html = jQuery('#quizzAppendFrom').children('#section_container').children('#section').children('#lesson_append_to').html();
      }

      var section_Serial = jQuery(this).parent().parent().parent().children('#section_ID').attr('data-serial');

      var serial_arr = [];
      jQuery(this).parent().parent().parent().children('#lesson_append_to').children('#lesson_container').each(function(i, v) {
        serial_arr.push(parseInt(jQuery(v).find('#lesson_ID').attr('data-serial')));
      });

      if (type == 'attest_quizz') {
        var quizz_arr = [];
        jQuery(this).parent().parent().parent().children('#lesson_append_to').children('#lesson_container').each(function(i, v) {
          quizz_arr.push(parseInt(jQuery(v).find('#lesson_ID').attr('data-quizz-serial')));
        });

        var sorted_quizz_arr = quizz_arr.filter(function (value) {return !Number.isNaN(value)}).sort((a,b) => a-b);
        var quizz_Serial = sorted_quizz_arr[sorted_quizz_arr.length-1];

        if (isNaN(quizz_Serial)) {
          var quizz_count = 0;
        } else if (quizz_Serial == 0) {
          var quizz_count = 1;
        } else {
          var quizz_count = parseInt(quizz_Serial) + 1;
        }
      }

      var sorted_serial_arr = serial_arr.filter(function (value) {return !Number.isNaN(value)}).sort((a,b) => a-b);
      var lesson_Serial = sorted_serial_arr[sorted_serial_arr.length-1];
      if (isNaN(lesson_Serial)) {
        var lesson_count = 0;
      } else if (lesson_Serial == 0) {
        var lesson_count = 1;
      } else {
        var lesson_count = parseInt(lesson_Serial) + 1;
      }

      var count_lessons = jQuery(this).closest('#section_append_to').find('#lesson_container').length;

      if (type == 'attest_lesson') {
        var altered_lesson_html = jQuery(lesson_html).find('#lesson_ID').attr('data-serial', lesson_count).end()
        .find('#attest_lesson_id').attr('name', 'attest_curriculum['+section_Serial+']['+lesson_count+'][lesson_id]').end()
        .find('#attest_lesson_teaser').attr('name', 'attest_curriculum['+section_Serial+']['+lesson_count+'][lesson_teaser]').end()
        .find('#attest_lesson_id>option[value="'+chosen_lesson_id+'"]').attr('selected', true).end()
        .find('.lesson-link-view').attr('href', chosen_lesson_url).end()
        .find('.lesson-link-edit').attr('href', chosen_lesson_edit_url).end()
        .find('#accordion_title_number_lesson').text(lesson_count+1).end()
        .find('#accordion_title_name_lesson').html(chosen_lesson_title).end();
      } else if (type == 'attest_quizz') {
        var altered_lesson_html = jQuery(lesson_html).find('#lesson_ID').attr('data-serial', lesson_count).end()
        .find('#lesson_ID').attr('data-quizz-serial', quizz_count).end()
        .find('#attest_quizz_id').attr('name', 'attest_curriculum['+section_Serial+']['+lesson_count+'][lesson_id]').end()
        .find('#attest_quizz_id>option[value="'+chosen_quizz_id+'"]').attr('selected', true).end()
        .find('.lesson-link-view').attr('href', chosen_quizz_url).end()
        .find('.lesson-link-edit').attr('href', chosen_quizz_edit_url).end()
        .find('#accordion_title_number_quizz').text(quizz_count+1).end()
        .find('#accordion_title_name_quizz').html(chosen_quizz_title).end();
      }
      jQuery(this).parent().parent().prev('#lesson_append_to').append(altered_lesson_html);

      //Add number to new lesson
      if (type == 'attest_lesson') {
        var count = jQuery('#lesson_assessment_count').text();
        var new_count = parseInt(count) + 1;
        jQuery('#lesson_assessment_count, #lesson_assessment_count_more').text(new_count);
      }

      //var form = jQuery(this).closest('.add-new-lesson').find('.new_lesson_form');
      //jQuery(form).find('#new_lesson_or_quizz_type_selector>option[value="exist"]').attr('selected', 'selected');
      //toggle_new_lesson_form('exist', form);

    } else if (selector == 'new' && ((title != undefined && title != '') || (quizz_title != undefined && quizz_title != '')) && course_id != undefined && course_id != '') {

      var option_id = false;
      var option_title = false;
      var option_html = false;
      var action = 'attest_new_lesson';

      if (type == 'attest_quizz') {
        action = 'attest_new_quizz';
        title = quizz_title;
      }

      jQuery.post(curriculum_ajax.url,
        {'action': action, 'title': title, 'course_id': course_id, 'nonce': nonce},
        function(response) {
          if ( response != '' && response != false && response != undefined ) {

            var data = JSON.parse(response);
            if (data.alert != 1) {

              alert(data.alert);
            } else {

              option_id = data.id;
              option_title = data.title;
              option_url = data.url.replace('&amp;', '&');
              option_edit_url = data.edit_url.replace('&amp;', '&');
              option_html = '<option value="' + data.id + '" data-title="' + data.title + '" data-link="' + data.url + '" data-edit-link="' + data.edit_url + '">' + data.title + '</option>';
              exist_html = '<option value="' + data.id + '">' +  data.title + '</option>';

              if (option_id != undefined) {

                if (type == 'attest_lesson') {
                  var lesson_html = jQuery('#appendFrom').children('#section_container').children('#section').children('#lesson_append_to').html();
                } else if (type == 'attest_quizz') {
                  var lesson_html = jQuery('#quizzAppendFrom').children('#section_container').children('#section').children('#lesson_append_to').html();
                }

                var section_Serial = jQuery(element).parent().parent().parent().children('#section_ID').attr('data-serial');

                var serial_arr = [];
                jQuery(element).parent().parent().parent().children('#lesson_append_to').children('#lesson_container').each(function(i, v) {
                  serial_arr.push(parseInt(jQuery(v).find('#lesson_ID').attr('data-serial')));
                });

                var quizz_arr = [];
                jQuery(element).parent().parent().parent().children('#lesson_append_to').children('#lesson_container').each(function(i, v) {
                  quizz_arr.push(parseInt(jQuery(v).find('#lesson_ID').attr('data-quizz-serial')));
                });
                var sorted_quizz_arr = quizz_arr.filter(function (value) {return !Number.isNaN(value)}).sort((a,b) => a-b);
                var quizz_Serial = sorted_quizz_arr[sorted_quizz_arr.length-1];
                if (isNaN(quizz_Serial)) {
                  var quizz_count = 0;
                } else if (quizz_Serial == 0) {
                  var quizz_count = 1;
                } else {
                  var quizz_count = parseInt(quizz_Serial) + 1;
                }

                var sorted_serial_arr = serial_arr.filter(function (value) {return !Number.isNaN(value)}).sort((a,b) => a-b);
                var lesson_Serial = sorted_serial_arr[sorted_serial_arr.length-1];
                if (isNaN(lesson_Serial)) {
                  var lesson_count = 0;
                } else if (lesson_Serial == 0) {
                  var lesson_count = 1;
                } else {
                  var lesson_count = parseInt(lesson_Serial) + 1;
                }

                if (!isNaN(quizz_count)) {
                  var display_lesson_count = ((sorted_serial_arr.length - sorted_quizz_arr.length) + 1);
                } else {
                  var display_lesson_count = lesson_count;
                }

                if (type == 'attest_lesson') {
                  jQuery('select[id="attest_lesson_id"]').each(function(i, v) {
                    jQuery(v).append(option_html);
                  });
                } else if (type == 'attest_quizz') {
                  jQuery('select[id="attest_quizz_id"]').each(function(i, v) {
                    jQuery(v).append(option_html);
                  });
                }

                if (type == 'attest_lesson') {
                  jQuery('select[id="attest_lesson_exist"]').each(function(i, v) {
                    jQuery(v).append(exist_html);
                  });
                } else if (type == 'attest_quizz') {
                  jQuery('select[id="attest_quizz_exist"]').each(function(i, v) {
                    jQuery(v).append(exist_html);
                  });
                }

                var count_lessons = jQuery(element).closest('#section_append_to').find('#lesson_container').length;

                if (type == 'attest_lesson') {
                  var altered_lesson_html = jQuery(lesson_html).find('#lesson_ID').attr('data-serial', lesson_count).end()
                  .find('#attest_lesson_id').attr('name', 'attest_curriculum['+section_Serial+']['+lesson_count+'][lesson_id]').end()
                  .find('#attest_lesson_teaser').attr('name', 'attest_curriculum['+section_Serial+']['+lesson_count+'][lesson_teaser]').end()
                  .find('#attest_lesson_id').append(option_html).end()
                  .find('#attest_lesson_id>option[value="'+option_id+'"]').attr('selected', true).end()
                  .find('#attest_new_lesson_exist').append(exist_html).end()
                  .find('.lesson-link-view').attr('href', option_url).end()
                  .find('.lesson-link-edit').attr('href', option_edit_url).end()
                  .find('#accordion_title_number_lesson').text(display_lesson_count).end()
                  .find('#accordion_title_name_lesson').html(option_title).end();
                } else if (type == 'attest_quizz') {
                  var altered_lesson_html = jQuery(lesson_html).find('#lesson_ID').attr('data-serial', lesson_count).end()
                  .find('#lesson_ID').attr('data-quizz-serial', quizz_count).end()
                  .find('#attest_quizz_id').attr('name', 'attest_curriculum['+section_Serial+']['+lesson_count+'][lesson_id]').end()
                  .find('#attest_quizz_id').append(option_html).end()
                  .find('#attest_quizz_id>option[value="'+option_id+'"]').attr('selected', true).end()
                  .find('#attest_new_quizz_exist').append(exist_html).end()
                  .find('.lesson-link-view').attr('href', option_url).end()
                  .find('.lesson-link-edit').attr('href', option_edit_url).end()
                  .find('#accordion_title_number_quizz').text(quizz_count+1).end()
                  .find('#accordion_title_name_quizz').html(option_title).end();
                }

                jQuery(element).parent().parent().prev('#lesson_append_to').append(altered_lesson_html);

                //Add number to new lesson
                var count = jQuery('#lesson_assessment_count').text();
                var new_count = parseInt(count) + 1;
                jQuery('#lesson_assessment_count, #lesson_assessment_count_more').text(new_count);


              }
            }
          }
        });

        //var form = jQuery(this).closest('.add-new-lesson').find('.new_lesson_form');
        //jQuery(form).find('#new_lesson_or_quizz_type_selector>option[value="new"]').attr('selected', 'selected');
        //toggle_new_lesson_form('new', form);
      }

      jQuery(this).parent().find('#add_new_lesson, #add_new_quizz').val('');

      jQuery(this).closest('.add-new-lesson').find('.new_lesson_form').hide();
      jQuery(this).closest('.add-new-lesson').find('.new_lesson_link').show();
  });


  //Delete lesson
  jQuery(document).delegate('.delete_lesson', 'click', function(){

    if (!confirm(curriculum_ajax.delete_lesson)){
      return false;
    } else {

      //The sequence matters here
      var father_container = jQuery(this).closest('#section_append_to');
      var mother_container = jQuery(this).closest('#lesson_append_to');
      var child_container = jQuery(this).closest('#lesson_container');

      jQuery(this).closest('#lesson_container').remove();

      var count_lessons_obj = father_container.find('#lesson_container');
      var lessons_number_obj = mother_container.find('span[id="accordion_title_number_lesson"]');

      lessons_number_obj.each(function(i,v){
        if (i < lessons_number_obj.length) {
          jQuery(v).text(i+1);
        }
      });

      var quizz_number_obj = mother_container.find('span[id="accordion_title_number_quizz"]');

      quizz_number_obj.each(function(i,v){
        if (i < quizz_number_obj.length) {
          jQuery(v).text(i+1);
        }
      });

      //Subtract number to new lesson
      var count = jQuery('#lesson_assessment_count').text();
      var new_count = parseInt(count) - 1;
      jQuery('#lesson_assessment_count, #lesson_assessment_count_more').text(new_count);
    }
  });


  //Change lesson name on accordion title
  jQuery(document).delegate('#attest_lesson_id, #attest_quizz_id', 'change', function() {
    var id = jQuery('option:selected', this).val();
    var option = jQuery('option:selected', this).attr('data-title');
    var link = jQuery('option:selected', this).attr('data-link');
    var edit_link = jQuery('option:selected', this).attr('data-edit-link');

    var container = jQuery(this).parent().parent().parent().parent().parent().parent('#lesson_container');

    container.find('.lesson-link-view').attr('href', link).end().find('.lesson-link-edit').attr('href', edit_link).end();
    container.find('#accordion_title_name_lesson').text(option);
    container.find('#accordion_title_name_quizz').text(option);
  });


  //Title editor
  jQuery(document).delegate('.edit_section', 'click', function(event){

    event.preventDefault();
    jQuery(this).hide();
    jQuery(this).parent().parent().find('#attest_curriculum_title, #save_title').show();
    jQuery(this).parent().parent().find('#accordion_title_name_section').text('');
  });


  //Edit the section title
  jQuery(document).delegate('.save_title', 'click', function(){

    var element = this;
    var id = jQuery(this).attr('data-id');
    var nonce = jQuery(this).parent().parent().find('#attest_save_lesson_nonce').val();
    var data = get_attest_curriculum(this);

    var title = jQuery(this).prev('#attest_curriculum_title').val();

    if (id != undefined && id != '') {

      jQuery(this).text(curriculum_ajax.saving_text);

      jQuery.post(curriculum_ajax.url,
        {'action': 'attest_save_lesson', 'course_id': id, 'data': data, 'nonce': nonce},
        function(response) {
          if ( response != '' && response != false && response != undefined ) {

            var data = JSON.parse(response);
            if (data.alert != 1) {
              alert(data.alert);
            } else {
              jQuery(element).text(curriculum_ajax.saved_text);
              jQuery(element).parent().parent().find('#accordion_title_name_section').text(title);
              jQuery(element).parent().find('#attest_curriculum_title, #save_title').hide();
              jQuery(element).parent().parent().find('.edit_section').show();
            }
          }
        });
    }
  });


  //Toggle type selector toggle
  jQuery(document).delegate('#choose_quizz_or_lesson', 'change', function(e){

    var selector = jQuery(this).parent().find('#new_lesson_or_quizz_type_selector option:selected').val();
    var type = jQuery('option:selected', this).val();
    var form = jQuery(this).parent('.new_lesson_form');

    toggle_new_lesson_form(type, selector, form);
    toggle_new_quizz_form(type, selector, form);
  });


  //Toggle lesson creation selector toggle
  jQuery(document).delegate('#new_lesson_or_quizz_type_selector', 'change', function(e){

    var type = jQuery(this).parent().find('#choose_quizz_or_lesson option:selected').val();
    var selector = jQuery('option:selected', this).val();
    var form = jQuery(this).parent('.new_lesson_form');

    if (type == undefined) {
      type = 'attest_lesson';
    }

    toggle_new_lesson_form(type, selector, form);
    toggle_new_quizz_form(type, selector, form);
  });


  //Add accordion feature to lessons and sections
  jQuery('.section_close').show();
  jQuery('.section_open').hide();
  jQuery('.section-wrap, .lesson-wrap').hide();
  jQuery(document).delegate('.components-panel_section_body-title', 'click', function(e){
    var toggle = true;
    if (jQuery(e.target).find('.edit_section, #attest_curriculum_title, #save_title').length <= 0) {
      toggle = false;
    }
    if (false != toggle) {
      jQuery(this).find('.section_close, .section_open').toggle();
      jQuery(this).next('.section-wrap').toggle();
    }
  });
  jQuery(document).delegate('.components-panel_lesson_body-title, .components-panel_quizz_body-title', 'click', function(){
    jQuery(this).find('.section_close, .section_open').toggle();
    jQuery(this).next().next('.lesson-wrap').toggle();
    jQuery(this).closest('#lesson_container').toggleClass('lesson_container');
  });


  //Toggle section link
  jQuery('#new_section_form').hide();
  jQuery('#new_section_link').click( function(){
    jQuery(this).hide();
    jQuery('#new_section_form').show();
  });


  //Toggle lesson link
  jQuery('.new_lesson_form').hide();
  jQuery(document).delegate('.new_lesson_link', 'click', function(){

    var form = jQuery(this).parent('.add-new-lesson').children('.new_lesson_form');

    jQuery(this).hide();
    jQuery(this).next('.new_lesson_form').show();

    var type = jQuery(this).parent().find('#choose_quizz_or_lesson option:selected').val();
    var selector = jQuery(this).parent().find('#new_lesson_or_quizz_type_selector option:selected').val();

    if (type == undefined) {
      type = 'attest_lesson';
    }

    //jQuery(form).children('#add_new_lesson_exist>option[value="new"]').attr('selected', 'selected');
    toggle_new_lesson_form(type, selector, form);
    toggle_new_quizz_form(type, selector, form);
  });


  //Make things sortable and change numbers
  jQuery(document).delegate('#lesson_append_to', 'mouseenter', function() {
    jQuery(this).sortable();
  });

  jQuery('#section_append_to, #lesson_append_to').on( 'sortstop', function( event, ui ) {

      var sections = jQuery('span[id="accordion_title_number_section"]');
      sections.each(function(i,v){
        if (i < sections.length) {
          jQuery(v).text(i+1);
        }
      });

      var elem = ui['item'];
      var mother_container = jQuery(elem).closest('#lesson_append_to');

      var lessons_number_obj = mother_container.find('span[id="accordion_title_number_lesson"]');

      lessons_number_obj.each(function(i,v){
        if (i < lessons_number_obj.length) {
          jQuery(v).text(i+1);
        }
      });

      var quizzs_number_obj = mother_container.find('span[id="accordion_title_number_quizz"]');

      quizzs_number_obj.each(function(i,v){
        if (i < quizzs_number_obj.length) {
          jQuery(v).text(i+1);
        }
      });
  });

  jQuery('#section_append_to').sortable();

});
