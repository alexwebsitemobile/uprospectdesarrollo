<?php
get_header();
the_post();
?>
<div class="container">
    <div class="row">
        <div class="col-sm-offset-2 col-sm-8">
            <div class="post-content-card">
                <div class="text-center">
                    <?php the_post_thumbnail('full', array('class' => 'img-responsives mgb20')); ?>
                </div>
                <div class="content">
                    <h2><?php the_title(); ?></h2>
                    <h4 class="date"><i class="fa fa-calendar"></i> Published date: <?php the_time('F jS, Y'); ?></h4>
                    <?php the_content(); ?>
                </div>
            </div>
            <div class="text-center mgb20">
                <a class="bm-blog" href="<?php echo home_url('blog'); ?>">
                    <i class="fa fa-arrow-left"></i> Back to blog menu
                </a>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>