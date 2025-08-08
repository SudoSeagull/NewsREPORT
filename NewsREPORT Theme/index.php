<?php get_header(); ?>
<div class="row">
  <div class="col-12">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article <?php post_class('mb-4'); ?>>
        <h2 class="h4"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <div class="entry"><?php the_content(); ?></div>
      </article>
    <?php endwhile; endif; ?>
  </div>
</div>
<?php get_footer(); ?>
