<?php
/**
 * Front Page template rendering Drudge-style sections
 */
get_header();

function cp_query_section($slug, $limit = 20){
    $args = [
        'post_type' => 'drg_link',
        'posts_per_page' => $limit,
        'ignore_sticky_posts' => true,
        'tax_query' => [[
            'taxonomy' => 'section',
            'field'    => 'slug',
            'terms'    => $slug
        ]],
        'orderby' => [
            'meta_value_num' => 'DESC',
            'date' => 'DESC',
        ],
        'meta_key' => '_drudge_weight',
    ];
    return new WP_Query($args);
}
?>

<div class="row">
  <div class="col-md-4">
    <h2 class="section-title"><?php esc_html_e('Left','drudge-theme'); ?></h2>
    <?php $q = cp_query_section('left', 25); if ($q->have_posts()) : ?>
      <ul class="list-unstyled">
      <?php while ($q->have_posts()) : $q->the_post(); $url = drudge_get_external_url(); ?>
        <li class="link-item"><a href="<?php echo $url; ?>" <?php echo drudge_rel_attr(); ?>><?php the_title(); ?></a></li>
      <?php endwhile; ?>
      </ul>
      <?php wp_reset_postdata(); ?>
    <?php else : ?>
      <p class="text-muted"><?php esc_html_e('No items yet.', 'drudge-theme'); ?></p>
    <?php endif; ?>
  </div>

  <div class="col-md-4">
    <h2 class="section-title"><?php esc_html_e('Middle','drudge-theme'); ?></h2>
    <?php $q = cp_query_section('middle', 25); if ($q->have_posts()) : ?>
      <ul class="list-unstyled">
      <?php while ($q->have_posts()) : $q->the_post(); $url = drudge_get_external_url(); ?>
        <li class="link-item"><a href="<?php echo $url; ?>" <?php echo drudge_rel_attr(); ?>><?php the_title(); ?></a></li>
      <?php endwhile; ?>
      </ul>
      <?php wp_reset_postdata(); ?>
    <?php else : ?>
      <p class="text-muted"><?php esc_html_e('No items yet.', 'drudge-theme'); ?></p>
    <?php endif; ?>
  </div>

  <div class="col-md-4">
    <h2 class="section-title"><?php esc_html_e('Right','drudge-theme'); ?></h2>
    <?php $q = cp_query_section('right', 25); if ($q->have_posts()) : ?>
      <ul class="list-unstyled">
      <?php while ($q->have_posts()) : $q->the_post(); $url = drudge_get_external_url(); ?>
        <li class="link-item"><a href="<?php echo $url; ?>" <?php echo drudge_rel_attr(); ?>><?php the_title(); ?></a></li>
      <?php endwhile; ?>
      </ul>
      <?php wp_reset_postdata(); ?>
    <?php else : ?>
      <p class="text-muted"><?php esc_html_e('No items yet.', 'drudge-theme'); ?></p>
    <?php endif; ?>
  </div>
</div>

<?php
// Ticker row
$tq = cp_query_section('ticker', 20);
if ($tq->have_posts()) : ?>
  <hr class="my-4" />
  <div class="ticker">
    <?php while ($tq->have_posts()) : $tq->the_post();
      $url = drudge_get_external_url(get_the_ID()); ?>
      <span class="me-3">• <a href="<?php echo $url; ?>" <?php echo drudge_rel_attr(); ?>><?php the_title(); ?></a></span>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>
<?php endif; ?>

<?php get_footer(); ?>
