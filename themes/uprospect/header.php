<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
    <head>
        <meta charset="<?php bloginfo('charset') ?>" />
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="description" content="<?php bloginfo('description') ?>" />
        <link rel="shortcut icon" href="<?php bloginfo(template_url); ?>/favicon.ico" type="image/x-icon">
        <link rel="icon" href="<?php bloginfo(template_url); ?>/favicon.ico" type="image/x-icon">
        <?php wp_head() ?>

    </head>

    <body <?php body_class() ?> itemscope itemtype="http://schema.org/WebPage">

        <?php
        do_action('before_main_content');
        get_template_part('components/bs-main-navbar');
        ?>

        <header>
            <?php get_template_part('templates/menu'); ?>
        </header>
