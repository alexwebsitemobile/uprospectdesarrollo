<div class="container-blue">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="brd-btn">
                    <?php
                    wp_nav_menu(
                            array(
                                'theme_location' => 'footer-menu',
                                'menu_class' => 'footer_menu'
                            )
                    );
                    ?>
                </div>
                <div class="copy">
                    <?php echo get_option('theme_options_footer'); ?>
                </div>
            </div>
        </div>
    </div>
</div>	