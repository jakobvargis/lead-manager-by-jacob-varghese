<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="site-header">
  <div class="container">
    <div class="site-branding">
      <h1 class="site-title"><a href="<?php echo home_url(); ?>"><?php bloginfo('name'); ?></a></h1>
      <div class="nav"> <a href="<?php echo home_url(); ?>">Home</a> | <a href="<?php echo site_url('/submit-lead/'); ?>">Submit Lead</a> </div>
    </div>
  </div>
</header>
<main class="container" role="main">
