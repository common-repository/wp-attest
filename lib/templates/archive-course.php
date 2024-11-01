<?php
/*
Template Name: Single course page
Template Post Type: attest_course
*/
?>
<?php require_once(ATTEST_LMS_PATH . 'lib/templates/class-course-archive-functions.php'); ?>
<?php
$functions = new ATTEST_LMS_COURSE_ARCHIVE_FUNCTIONS();

global $wp_query;

get_header(); ?>

  <div class="container mt-5 mb-5">
    <div class="row">
      <div class="col-md-8 col-sm-12">

        <?php
        $term = get_term_by( 'slug', get_query_var('term'), get_query_var('taxonomy') );
        if ($term) : ?>
          <h2 class="mt-0 ml-0 mb-5 attest-course-tax-title"><?php echo esc_attr($term->name); ?></h2>
        <?php endif; ?>

        <?php
        if (have_posts() ) :
          while ( have_posts() ) : the_post();

          $ID              = get_the_ID();
          $featured        = get_post_meta( $ID, 'attest_course_featured', true);
          $enrollment_data = $functions->get_enrollment_data($ID);
          $first_lesson    = $functions-> get_course_first_lesson($ID);
          $thumbnail_url   = esc_url(get_the_post_thumbnail_url( $ID, 'post-thumbnail' )); ?>
        <div class="row attest-course-item course-<?php echo $ID . (($featured == '1' && !is_user_logged_in()) ? ' course-featured' : false); ?>">

          <?php if ( false != $thumbnail_url ) : ?>
          <div class="col-md-4 attest-course-thumb" style="background-image: url('<?php echo $thumbnail_url; ?>')">
            <?php if ($featured == '1' && !is_user_logged_in() ) : ?>
              <span class="badge badge-featured"><?php _e('FEATURED', 'attest'); ?></span>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <div class="col-md-<?php echo (false != $thumbnail_url ? '8' : '12'); ?>">
            <div class="media-body attest-course-item-content">
              <a class="attest-course-link" href="<?php echo ((false != $enrollment_data['enrolled']) ? esc_url_raw(get_permalink($first_lesson)) : esc_url_raw(get_permalink($ID)) ); ?>">
                <h4 class="mt-0 attest-course-title"><?php echo esc_attr(get_the_title()); ?></h4>
              </a>
              <div class="course-meta">
                <span><?php echo __('Created by', 'attest') . '&nbsp;' .  get_the_author(); ?></span>

                <?php if (!is_user_logged_in()) : ?>
                  <p class="attest-course-price"><strong><?php _e('FREE', 'attest'); ?></strong></p>
      			      <small><?php _e('Requires registration', 'attest'); ?></small>
                <?php else :

                  echo $enrollment_data['html'];
                endif; ?>

              </div>
            </div>
          </div>
        </div>
        <?php
        endwhile;

        posts_nav_link();

        wp_reset_postdata();
      endif; ?>
      </div>
      <div class="col-md-4 col-sm-12">
        <?php if ( is_active_sidebar( 'attest_course_archive_sidebar' ) ) : ?>
          <div class="attest-archive-sidebar">
            <?php dynamic_sidebar('attest_course_archive_sidebar'); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

<?php get_footer(); ?>
