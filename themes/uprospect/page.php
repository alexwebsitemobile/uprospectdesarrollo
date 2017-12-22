<?php
get_header();
the_post();
?>
<div class="container">
    <div class="row">
        <div class="col-xs-12 post-content">
            <?php the_content(); ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>