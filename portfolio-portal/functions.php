<?php

add_action('wp_enqueue_scripts', function() {
  wp_enqueue_style('pp-style', get_stylesheet_uri());
  wp_enqueue_script('pp-main', get_template_directory_uri() . '/js/main.js', array('jquery'), null, true);
  wp_localize_script('pp-main', 'pp_ajax', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('pp_nonce')));
});
// Enable title tag and other supports
add_theme_support('title-tag');
add_theme_support('post-thumbnails');

// Register CPTs
function pp_register_cpts() {
  // Clients
  register_post_type('client', array(
    'labels' => array('name' => 'Clients', 'singular_name' => 'Client'),
    'public' => true,
    'has_archive' => true,
    'rewrite' => array('slug' => 'clients'), // ✅ This line is crucial
    'show_in_rest' => true,
    'supports' => array('title','editor','thumbnail'),
    'menu_icon' => 'dashicons-businessman'
  ));

  // Leads
  register_post_type('lead', array(
    'labels' => array('name' => 'Leads', 'singular_name' => 'Lead'),
    'public' => false,
    'show_ui' => true,
    'show_in_rest' => true,
    'supports' => array('title','editor'),
    'menu_icon' => 'dashicons-index-card'
  ));
}
add_action('init', 'pp_register_cpts');


function pp_lead_meta_box() {
  add_meta_box('pp_lead_meta', 'Lead Info', 'pp_lead_meta_cb', 'lead', 'normal', 'high');
}
add_action('add_meta_boxes', 'pp_lead_meta_box');


function pp_lead_meta_cb($post) {
  $phone = get_post_meta($post->ID, '_pp_phone', true);
  $email = get_post_meta($post->ID, '_pp_email', true);
  $status = get_post_meta($post->ID, '_pp_status', true);
  wp_nonce_field('pp_save_lead', 'pp_lead_nonce');
  ?>
  <p><label>Phone</label><br><input type="text" name="pp_phone" value="<?php echo esc_attr($phone); ?>"></p>
  <p><label>Email</label><br><input type="email" name="pp_email" value="<?php echo esc_attr($email); ?>"></p>
  <p><label>Status</label><br>
    <select name="pp_status">
      <option value="new" <?php selected($status,'new'); ?>>New</option>
      <option value="contacted" <?php selected($status,'contacted'); ?>>Contacted</option>
      <option value="converted" <?php selected($status,'converted'); ?>>Converted</option>
    </select>
  </p>
  <?php
}


function pp_save_lead_meta($post_id) {
  if (!isset($_POST['pp_lead_nonce']) || !wp_verify_nonce($_POST['pp_lead_nonce'],'pp_save_lead')) return;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (isset($_POST['pp_phone'])) update_post_meta($post_id, '_pp_phone', sanitize_text_field($_POST['pp_phone']));
  if (isset($_POST['pp_email'])) update_post_meta($post_id, '_pp_email', sanitize_email($_POST['pp_email']));
  if (isset($_POST['pp_status'])) update_post_meta($post_id, '_pp_status', sanitize_text_field($_POST['pp_status']));
}
add_action('save_post_lead', 'pp_save_lead_meta');


function pp_testimonials_shortcode($atts) {
  $clients = get_posts(array('post_type'=>'client','numberposts'=>5));
  ob_start();
  echo '<div class="pp-testimonials">';
  foreach($clients as $c) {
    echo '<div class="pp-testimonial">';
    echo '<h4>'.esc_html(get_the_title($c)).'</h4>';
    echo '<div>'.wp_trim_words(apply_filters('the_content', $c->post_content),30).'</div>';
    echo '</div>';
  }
  echo '</div>';
  return ob_get_clean();
}
add_shortcode('testimonials','pp_testimonials_shortcode');




