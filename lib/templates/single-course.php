<?php
/*
Template Name: Single course page
Template Post Type: attest_course
*/
?>
<?php require_once(ATTEST_LMS_PATH . 'lib/templates/class-course-temp-functions.php'); ?>
<?php get_header(); ?>

<style type="text/css">
.material-icons, .announcement-date {
  vertical-align: bottom;
}
.faq-add {
  vertical-align: top;
}
</style>

<main id="site-content" class="mb-5" role="main">
  <div class="container">

    <?php
    //Get current post
    $current_post_ID = get_the_ID();
    $post = get_post($current_post_ID);

    //Get user detail
    $user_ID = get_current_user_id();

    //Get options
    $register_post_id = get_option('attest_template_register');
    $register_permalink = get_permalink($register_post_id);

    //Get post basic details
    $title = get_the_title($current_post_ID);
    $difficulty = wp_get_post_terms( $current_post_ID, 'difficulty', array( 'fields' => 'names' ) );
    $skills = wp_get_post_terms( $current_post_ID, 'post_tag', array( 'fields' => 'names' ) );
    $author_ID = get_post_field( 'post_author', $current_post_ID );
    $author = get_the_author_meta( 'display_name', $author_ID);
    $author_title = get_the_author_meta( 'attest_about_author', $author_ID);
    $author_gravatar = get_avatar_url($author_ID);
    $author_bio = get_the_author_meta( 'user_description', $author_ID);
    $image_src = get_the_post_thumbnail_url( $current_post_ID, 'post-thumbnail' );
    $content = $post->post_content;

    //Get meta data and prepare them
    $functions = new ATTEST_LMS_COURSE_FUNCTIONS();
    $topics = $functions->topic_links($current_post_ID);
    $author_links = $functions->author_links($author_ID);
    $video = $functions->get_video($current_post_ID);
    $language = $functions->get_course_language($current_post_ID);
    $announcement = $functions->get_course_announcement($current_post_ID);
    $key_features = $functions->get_course_features($current_post_ID);
    $requirements = $functions->get_course_requirements($current_post_ID);
    $audience = $functions->get_course_audience($current_post_ID);
    $curriculum = $functions->get_course_curriculum($current_post_ID);
    $faqs = $functions->get_course_faq($current_post_ID);
    $enrolled_data = $functions->enroll_to_course($current_post_ID);
    $students_count = $functions->count_registered_student($current_post_ID);
    $view_condition = $functions->view_course($current_post_ID, $user_ID);
    $price_url = $functions->get_paid_url($current_post_ID);
    $price = $functions->get_course_price($current_post_ID);

    $functions->update_student($current_post_ID, $user_ID);
    ?>

    <div class="row">
      <div class="col-md-12">
        <h2><?php echo esc_attr($title); ?></h2>
        <div class="row">
          <div class="col-md-12">
            <?php echo '<small>' . __('Created by', 'attest') . '&nbsp;<u>' . esc_attr($author) . '</u></small>'; ?>
            <?php echo (count($topics) > 0 ? '<small class="ml-1">' . __('in', 'attest') . '&nbsp;<u>' . implode(',&nbsp;', $topics) . '</u></small>' : false); ?>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8 mt-5">
        <div class="mb-5">
          <?php if ($video) : ?>
            <div class="embed-responsive embed-responsive-16by9">
              <?php echo $video; ?>
            </div>
          <?php else: ?>
          <img src="<?php echo esc_url($image_src); ?>" class="img-fluid" alt="<?php echo esc_html($title); ?>">
          <?php endif; ?>
        </div>

        <?php if(false != $announcement) : ?>
        <div class="attest-course-alert mt-5 mb-5">
          <div class="mb-4">
            <strong class="text-left attest-text-alert"><i class="material-icons">notifications_active</i>
            <span><?php echo esc_attr($announcement['title']); ?></span></strong>
            <small class="float-right text-muted announcement-date"><?php echo esc_attr($announcement['date']); ?></small>
          </div>
          <p><?php echo wp_kses_post($announcement['description']); ?></p>
        </div>
        <?php endif; ?>

        <?php if(false != $key_features) : ?>
        <div class="mt-5 mb-5">
          <h4><?php _e( 'What you will learn', 'attest'); ?></h4>
          <div class="row">
            <?php $feat_len = count($key_features);
            $half_features_first = array_slice($key_features, 0, ceil($feat_len / 2));
            $half_features_second = array_slice($key_features, ceil($feat_len / 2)); ?>
            <div class="col-md-6">
            <?php foreach($half_features_first as $first_feature) : ?>
              <div class="mt-2 mb-2">
                <p><i class="material-icons mr-3">check</i><?php echo wp_kses_post($first_feature); ?></p>
              </div>
            <?php endforeach; ?>
            </div>
            <div class="col-md-6">
            <?php foreach($half_features_second as $second_feature) : ?>
              <div class="mt-2 mb-2">
                <p><i class="material-icons mr-3">check</i><?php echo wp_kses_post($second_feature); ?></p>
              </div>
            <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <?php if(false != $requirements) : ?>
        <div class="mt-5 mb-5">
          <h4><?php _e( 'Requirements', 'attest'); ?></h4>
          <?php foreach($requirements as $req) : ?>
          <div class="mt-2 mb-2">
            <p><i class="material-icons mr-3">chevron_right</i><?php echo wp_kses_post($req); ?></p>
          </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if(false != $audience) : ?>
        <div class="mt-5 mb-5">
          <h4><?php _e( 'This course is good for', 'attest'); ?></h4>
          <?php foreach($audience as $audi) : ?>
          <div class="mt-2 mb-2">
            <p><i class="material-icons mr-3">remove</i><?php echo wp_kses_post($audi); ?></p>
          </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if(false != $curriculum) : ?>
        <div class="mt-5">
          <h4><?php _e( 'Curriculum', 'attest'); ?></h4>
          <div class="mt-5">
            <?php $item = 1;
            foreach($curriculum['data'] as $key => $section) : ?>
              <h6 class="mt-5 mb-3"><?php echo esc_attr($section['title']); ?></h6>
              <?php unset($section['title']); ?>
              <?php foreach($section[0] as $count => $lesson) : ?>
                <?php echo ((false != $view_condition || false != $enrolled_data['enrolled']) ? '<a class="badge-curriculum-link" href="' . esc_url_raw(get_permalink($lesson['lesson_id'])) . '">' : false ); ?>
                <div class="badge-curriculum text-left mb-2<?php echo ((isset($lesson['lesson_teaser']) && $lesson['lesson_teaser'] == '1') ? ' intro-video' : false ); ?>" <?php echo ((isset($lesson['lesson_teaser']) && $lesson['lesson_teaser'] == '1') ? ' data-toggle="modal" data-target=".attest-modal-_' . $count . '"' : false ); ?>>
                  <?php if (isset($lesson['lesson_teaser']) && $lesson['lesson_teaser'] == '1') : ?>
                    <i class="material-icons text-muted">play_circle_filled</i>
                  <?php else: ?>
                    <?php if (false != $view_condition || false != $enrolled_data['enrolled']) : ?>
                      <i class="material-icons text-muted">play_circle_filled</i>
                    <?php else: ?>
                      <i class="material-icons text-muted">lock</i>
                    <?php endif; ?>
                  <?php endif; ?>
                  <?php echo '&nbsp;<span class="mb-2">' . intval($item) . '.&nbsp;' . esc_attr(get_the_title($lesson['lesson_id'])) . '</span>'; ?>
                  <span class="float-right text-muted"><?php echo $functions->format_duration($lesson['lesson_id']); ?></span>
                </div>
                <?php echo ((false != $view_condition || false != $enrolled_data['enrolled']) ? '</a>' : false ); ?>

                <?php if(isset($lesson['lesson_teaser']) && $lesson['lesson_teaser'] == '1') : ?>
                <!-- Intro Modal -->
                <div class="modal fade bd-example-modal-lg attest-modal-_<?php echo $count; ?>" id="attestIntroVideoModal_<?php echo $count; ?>" tabindex="-1" role="dialog" aria-labelledby="attestIntroVideoLabel_<?php echo $count; ?>">
                  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title mt-0 ml-0 mb-2" id="attestIntroVideoLabel_<?php echo $count; ?>">1. <?php echo esc_attr(get_the_title($lesson['lesson_id'])); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body" id="modal_body_<?php echo $count; ?>" style="height: 500px; display: block;">
                        <?php echo $functions->get_video($lesson['lesson_id']); ?>
                      </div>
                    </div>
                  </div>
                </div>
                <script>
                  var video_<?php echo $count; ?> = jQuery('#attestIntroVideoModal_<?php echo $count; ?>').find('#modal_body_<?php echo $count; ?>').html();
                  jQuery('.bd-example-modal-lg').on('shown.bs.modal', function() {
                    jQuery(this).find('#modal_body_<?php echo $count; ?>').html(video_<?php echo $count; ?>);
                  });
                </script>
              <?php endif; ?>
              <?php $item++; endforeach; ?>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if(false != $content) : ?>
        <div class="mt-5 mb-5">
          <h4><?php _e( 'Description', 'attest'); ?></h4>
          <?php echo wp_kses_post($content); ?>
        </div>
        <?php endif; ?>

        <?php if(false != $faqs) : ?>
        <div class="mt-5">
          <h4><?php _e( 'Frequent Asked Questions', 'attest'); ?></h4>
          <div class="mt-5">
            <div class="accordion" id="FAQ">
              <?php foreach($faqs as $key => $faq) : ?>
                <div class="card">
                  <div class="card-header" id="heading<?php echo intval($key); ?>">
                    <div class="card-header-btn btn btn-block" type="button" data-toggle="collapse" data-target="#collapseFAQ<?php echo intval($key); ?>" aria-expanded="false" aria-controls="collapse<?php echo intval($key); ?>" style="font-size: inherit !important;">
                      <strong class="float-left"><?php echo esc_attr($faq['q']); ?></strong>
                      <strong class="float-right"><i class="faq-add material-icons">add</i></strong>
                    </div>
                  </div>
                  <div id="collapseFAQ<?php echo intval($key); ?>" class="collapse <?php echo ($key == 0 ? 'show' : false); ?>" aria-labelledby="heading<?php echo intval($key); ?>" data-parent="#FAQ">
                    <div class="card-body">
                      <div class="mt-2 mb-2 ml-3">
                        <p><?php echo esc_attr($faq['a']); ?></p>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <?php if(false != $skills) : ?>
        <div class="mt-5 mb-5">
          <h4><?php _e( 'Tags', 'attest'); ?></h4>
          <?php echo '<span class="badge badge-skills">' . implode('</span><span class="ml-4 badge badge-skills">', $skills) . '</span>'; ?>
        </div>
        <?php endif; ?>

        <h4><?php _e('About Author', 'attest'); ?></h4>
        <div class="media" style="font-size: inherit !important;">
          <img src="<?php echo esc_url($author_gravatar); ?>" class="rounded-circle mr-3" alt="Teacher photo">
          <div class="media-body">
            <h5 class="mt-0 mb-2"><?php echo esc_attr($author); ?></h5>
            <small class="text-muted mb-4"><?php echo esc_attr($author_title); ?></small>
          </div>
        </div>
        <div class="mt-5">
          <p><?php echo wp_kses_post($author_bio); ?></p>
        </div>
        <div class="mt-2">
          <?php echo $author_links; ?>
        </div>
      </div>

      <div class="col-md-3 offset-md-1">
        <p class="mt-5 mb-0"><?php echo $price; ?></p>

        <div class="mt-4 mb-0">
          <?php if($enrolled_data['message']) : ?>
          <div class="attest-course-alert mt-3 mb-3">
            <small><?php echo esc_attr($enrolled_data['message']); ?></small>
          </div>
          <?php endif; ?>
          <?php if (is_user_logged_in()) : ?>
            <?php if (false == $view_condition && false == $enrolled_data['enrolled']) : ?>
              <form action="" method="post">
                <?php wp_nonce_field( 'attest_enroll_nonce_action', 'attest_enroll_nonce' ); ?>
                <input type="hidden" name="attest_course_id" value="<?php echo intval($current_post_ID); ?>" />
                <input type="hidden" name="attest_student_id" value="<?php echo intval($user_ID); ?>" />
                <input type="submit" name="attest_enroll_course" class="attest-button-block" value="<?php _e('Enroll', 'attest'); ?>" />
              </form>
            <?php else : ?>
              <a href="<?php echo esc_url_raw(get_permalink($curriculum['first_lesson'])); ?>" class="btn btn-danger attest-button-block"><?php _e('Go to Lesson', 'attest'); ?></a>
            <?php endif; ?>
          <?php else: ?>
            <?php if( $price_url ) : ?>
              <form action="" method="post">
                <input type="hidden" name="attest_course_id" value="<?php echo intval($current_post_ID); ?>" />
                <input type="submit" name="attest_enroll_course_logged_out" class="attest-button-block" value="<?php _e('Enroll', 'attest'); ?>" />
              </form>
            <?php else: ?>
              <a href="<?php echo esc_url_raw($register_permalink . '?course_ref=' . $current_post_ID); ?>" class="btn btn-danger attest-button-block"><?php _e('Enroll', 'attest'); ?></a>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <p class="mt-4 mb-5"><small><?php printf( _n( '%s Student enrolled', '%s Students enrolled', $students_count, 'attest' ), $students_count ); ?></small></p>
        <?php if(count($difficulty) > 0) : ?>
        <div class="row mt-0 mb-2">
          <div class="col-md-6"><span><?php _e('Difficulty', 'attest'); ?></span></div>
          <div class="col-md-6 text-right"><strong class="attest-break-by-word"><?php echo esc_attr(implode(', ', $difficulty)); ?></strong></div>
        </div>
        <?php endif; ?>
        <?php if($language) : ?>
        <div class="row mt-0 mb-2">
          <div class="col-md-6"><span><?php _e('Language', 'attest'); ?></span></div>
          <div class="col-md-6 text-right"><strong class="attest-break-by-word"><?php echo esc_attr($language); ?></strong></div>
        </div>
        <?php endif; ?>
        <?php if (false != $curriculum['duration']) : ?>
        <div class="row mt-0 mb-2">
          <div class="col-md-6"><span><?php _e('Duration', 'attest'); ?></span></div>
          <div class="col-md-6 text-right"><strong class="attest-break-by-word"><?php echo esc_attr($curriculum['duration']); ?></strong></div>
        </div>
        <?php endif; ?>
        <?php if(isset($curriculum['lesson'])) : ?>
        <div class="row mt-0 mb-2">
          <div class="col-md-6"><span><?php _e('Lessons', 'attest'); ?></span></div>
          <div class="col-md-6 text-right"><strong class="attest-break-by-word"><?php echo esc_attr($curriculum['lesson']); ?></strong></div>
        </div>
      <?php endif; ?>
      </div>

    </div>
  </div>
</main>
<?php get_footer(); ?>
