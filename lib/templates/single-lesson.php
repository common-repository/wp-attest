<?php
/*
Template Name: Single Lesson page
Template Post Type: attest_lesson
*/
?>
<?php require_once(ATTEST_LMS_PATH . 'lib/templates/class-lesson-temp-functions.php'); ?>
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

    $congrats_post_id = get_option('attest_template_congrats');
    $congrats_permalink = get_permalink($congrats_post_id);

    //Get post basic details
    $title = get_the_title($current_post_ID);
    $image_src = get_the_post_thumbnail_url( $current_post_ID, 'post-thumbnail' );
    $content = $post->post_content;

    //Get meta data and prepare them
    $functions = new ATTEST_LMS_LESSON_FUNCTIONS();
    $video_type = $functions->get_video_type($current_post_ID);
    $video = $functions->get_video($current_post_ID);
    $duration = $functions->format_duration($current_post_ID);

    $course = $functions->get_related_course($current_post_ID);
    $nav_lesson = $functions->nav_lessons($current_post_ID, $course);
    $curriculum = $functions->get_course_curriculum($course);

    $functions->lesson_loaded($current_post_ID, $user_ID);
    ?>

    <div class="row">
      <div class="col-md-12">
        <small class="text-muted"><?php _e('Part of', 'attest'); ?> <a href="<?php echo esc_url_raw(get_permalink($course)); ?>"><?php echo esc_attr(get_the_title($course)); ?></a></small>
        <h2 class="mt-1"><?php echo esc_attr($title); ?></h2>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <a class="attest-lesson-nav-link float-left" href="<?php echo (($nav_lesson['pre'] != false) ? esc_url_raw(get_permalink($nav_lesson['pre'])) : '#'); ?>"><?php echo (($nav_lesson['pre'] != false) ? '&lsaquo;&nbsp;&nbsp;&nbsp;' . __('PREVIOUS', 'attest') : false ); ?></a>
        <a class="attest-lesson-nav-link float-right" href="<?php echo (($nav_lesson['post'] != false) ? esc_url_raw(get_permalink($nav_lesson['post'])) : esc_url_raw($congrats_permalink) . '?course=' . $course); ?>"><?php echo (($nav_lesson['post'] != false) ? __('NEXT', 'attest') . '&nbsp;&nbsp;&nbsp;&rsaquo;' : __('COMPLETE', 'attest') ); ?></a>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-md-8 mt-5">
        <div class="mb-3">
          <?php if ($video) : ?>
          <div class="embed-responsive<?php echo ($video_type != 'wistia_code' ? 'embed-responsive-16by9' : false ); ?>">
            <?php echo $video; ?>
          </div>
          <?php else: ?>
          <img src="<?php echo esc_url($image_src); ?>" class="img-fluid" alt="<?php echo esc_html($title); ?>">
          <?php endif; ?>
        </div>
        <div class="mt-3">
          <span><small class="text-muted"><?php _e('Duration', 'attest'); echo ' <strong>' . esc_attr($duration) . '</strong>'; ?></small></span>
        </div>

        <?php if(false != $content) : ?>
        <div class="mt-5 mb-5">
          <h4><?php _e( 'Description', 'attest'); ?></h4>
          <?php echo wp_kses_post($content); ?>
        </div>
        <?php endif; ?>

      </div>

      <div class="col-md-4">

        <?php if(false != $curriculum) : ?>
        <div class="attest-curriculum-sidebar">
          <div class="mt-5">
            <?php $item = 1;
            foreach($curriculum['data'] as $key => $section) : ?>
              <h6 class="mt-5 mb-3"><?php echo esc_attr($section['title']); ?></h6>
              <?php unset($section['title']); ?>
              <?php foreach($section[0] as $count => $lesson) : ?>
                <?php $student_list_data = get_post_meta( $lesson['lesson_id'], 'attest_enrolled_students', false );
                $student_list = (isset($student_list_data[0]) ? $student_list_data[0] : array());
                ?>
                <a class="badge-curriculum-link" href="<?php echo esc_url_raw(get_permalink($lesson['lesson_id'])); ?>">
                <div class="badge-curriculum text-left mb-2 <?php echo ( ($lesson['lesson_id'] == $current_post_ID) ? ' attest-lesson-active' : false ); ?>">
                  <?php if ($lesson['lesson_id'] == $current_post_ID) : ?>
                    <i class="material-icons icon-small text-attest">play_circle_filled</i>
                  <?php else: ?>
                    <?php if (is_array($student_list) && in_array($user_ID, $student_list)) : ?>
                      <i class="material-icons icon-small text-muted">check</i>
                    <?php else: ?>
                      <i class="material-icons icon-small text-muted">play_circle_filled</i>
                    <?php endif; ?>
                  <?php endif; ?>
                  <span><?php echo intval($item) . '.&nbsp;' . esc_attr(substr(get_the_title($lesson['lesson_id']),0,20)); ?></span>
                  <span class="float-right text-muted"><?php echo $functions->format_duration($lesson['lesson_id']); ?></span>
                </div>
                </a>
              <?php $item++; endforeach; ?>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

      </div>

    </div>
  </div>
</main>
<?php get_footer(); ?>
