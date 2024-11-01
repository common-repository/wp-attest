jQuery(document).ready(function(){

  //Delete faq table
  jQuery(document).delegate('.delete_q_a', 'click', function(){
    if (!confirm(data.delete)){
      return false;
    } else {
      var container = jQuery(this).closest('#attest_faq_append_to');
      jQuery(this).closest('#attest_faq_container').remove();
      var number_obj = container.find('span[id="attest_faq_number"]');
      number_obj.each(function(i,v){
        if (i < number_obj.length) {
          jQuery(v).text(i+1);
        }
      });
    }
  });

  //Create faq table
  jQuery(document).delegate('#attest_faq_add_new', 'click', function() {
    var faq_html = jQuery('#attest_template').html();
    var faq_Serial = jQuery(this).parent().parent().children('#attest_faq_append_to').children('#attest_faq_container').last().find('#attest_fq_id').text();
    var faq_display_serial = jQuery(this).parent().parent().children('#attest_faq_append_to').children('#attest_faq_container').length;

    if (!faq_Serial) {
      var faq_count = 0;
    } else {
      var faq_count = parseInt(faq_Serial) + 1;
    }

    if (!faq_display_serial) {
      faq_display_serial = 1;
    } else {
      faq_display_serial = parseInt(faq_display_serial) + 1;
    }

    var altered_faq_html = jQuery(faq_html).find('#attest_fq_id').text(faq_count).end()
    .find('#attest_faq_number').text(faq_display_serial).end()
    .find('#attest_faq_q').attr('name', 'attest_faq['+faq_count+'][q]').end()
    .find('#attest_faq_a').attr('name', 'attest_faq['+faq_count+'][a]').end();

    jQuery(this).parent().parent().find('#attest_faq_append_to').append(altered_faq_html);
  });
});
