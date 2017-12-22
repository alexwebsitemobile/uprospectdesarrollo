<?php
get_header();
?>
<div class="container">
    <div class="row">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                <div class="col-md-offset-2 col-md-8 col-sm-offset-1 col-sm-10">
                    <div class="post-card">
                        <div class="row">
                            <div class="col-xs-4 no-padding tg-verticalmiddle">
                                <a href="<?php the_permalink(); ?>" class="entry-image">
                                    <?php
                                    if (has_post_thumbnail()) {
                                        the_post_thumbnail('blogimage', array('class' => 'img-responsive'));
                                    } else {
                                        ?>
                                    <img class="img-responsive" src="<?php bloginfo('template_url'); ?>/images/place.png" alt="<?php the_title(); ?>">
                                        <?php
                                    }
                                    ?>
                                    <div class="pattern"></div>
                                    <div class="show-more animated">
                                        <i class="fa fa-plus-square-o"></i>
                                    </div>
                                </a>
                            </div>
                            <div class="col-xs-8 tg-verticalmiddle">
                                <div class="content-card-blog">
                                    <h2>
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </h2>
                                    <div class="excerpt">
                                        <?php the_excerpt(); ?>
                                    </div>
                                    <div class="btn-container text-center">
                                        <a href="<?php the_permalink(); ?>">
                                            Read More
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <?php get_template_part('templates/bs-default-pagination'); ?>
                    </div>
                </div>
                <?php
            endwhile;
        endif;
        ?>
    </div>
</div>
<?php get_footer(); ?>