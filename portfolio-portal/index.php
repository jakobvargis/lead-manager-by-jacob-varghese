<?php get_header(); ?>
<section>
  <h2>Welcome — Portfolio Portal Demo</h2>
  <p>This is a minimal theme showing the lead capture and client CPTs.</p>

  <div class="lead-list">
    <h3>Latest Leads (admin view will have details)</h3>
    <?php
    $leads = get_posts(array('post_type'=>'lead','numberposts'=>6));
    if ($leads){
      echo '<ul>';
      foreach($leads as $l){
        $email = get_post_meta($l->ID,'_pp_email',true);
        $phone = get_post_meta($l->ID,'_pp_phone',true);
        echo '<li><strong>'.esc_html($l->post_title).'</strong> — '.esc_html($email).' ('.esc_html($phone).')</li>';
      }
      echo '</ul>';
    } else {
      echo '<p>No leads yet. Add a lead via the <a href="'.site_url('/submit-lead/').'">lead form</a>.</p>';
    }
    ?>
  </div>

  <?php echo do_shortcode('[testimonials]'); ?>

</section>
<?php get_footer(); ?>
