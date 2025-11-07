<?php
/**
 * Template for displaying single Client with admin edit form
 */

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();
        $client_id = get_the_ID();
        $email = get_post_meta($client_id, '_pp_email', true);
        $phone = get_post_meta($client_id, '_pp_phone', true);

        // Handle form submission
        if (isset($_POST['client_update_nonce']) && wp_verify_nonce($_POST['client_update_nonce'], 'client_update_action')) {
            if (current_user_can('edit_post', $client_id)) {
                $new_email = sanitize_email($_POST['client_email']);
                $new_phone = sanitize_text_field($_POST['client_phone']);
                update_post_meta($client_id, '_pp_email', $new_email);
                update_post_meta($client_id, '_pp_phone', $new_phone);
                echo '<div style="max-width:800px;margin:20px auto;padding:10px;background:#dff0d8;color:#3c763d;border-radius:6px;text-align:center;">Client details updated successfully!</div>';
                // Refresh values
                $email = $new_email;
                $phone = $new_phone;
            }
        }
?>
<main class="single-client container" style="max-width:800px;margin:50px auto;padding:0 20px;">

    <article class="client-detail" style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:30px;box-shadow:0 3px 8px rgba(0,0,0,0.08);">
        
        <?php if (has_post_thumbnail()) : ?>
        <div class="client-thumb" style="text-align:center;margin-bottom:25px;">
            <?php the_post_thumbnail('large', ['style'=>'border-radius:10px;width:100%;height:auto;']); ?>
        </div>
        <?php endif; ?>

        <h1 style="font-size:2em;margin-bottom:10px;color:#222;"><?php the_title(); ?></h1>

        <ul style="list-style:none;padding:0;margin-bottom:20px;font-size:1em;color:#333;">
            <?php if ($email): ?>
                <li><strong>Email:</strong> <?php echo esc_html($email); ?></li>
            <?php endif; ?>
            <?php if ($phone): ?>
                <li><strong>Phone:</strong> <?php echo esc_html($phone); ?></li>
            <?php endif; ?>
        </ul>

        <div class="client-content" style="font-size:1.05em;line-height:1.6;color:#444;">
            <?php the_content(); ?>
        </div>

        <div style="margin-top:30px;text-align:center;">
            <a href="<?php echo esc_url(get_post_type_archive_link('client')); ?>" 
               style="display:inline-block;background:#0073aa;color:#fff;text-decoration:none;padding:10px 20px;border-radius:5px;transition:background .2s;">
                ← Back to Clients
            </a>
        </div>

        <?php if (current_user_can('edit_post', $client_id)) : ?>
        <hr style="margin:40px 0;">
        <section style="margin-top:30px;">
            <h2 style="font-size:1.4em;margin-bottom:15px;">✏️ Edit Client Info</h2>
            <form method="post" style="display:flex;flex-direction:column;gap:10px;max-width:400px;">
                <label>Email:
                    <input type="email" name="client_email" value="<?php echo esc_attr($email); ?>" required 
                           style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
                </label>
                <label>Phone:
                    <input type="text" name="client_phone" value="<?php echo esc_attr($phone); ?>" 
                           style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
                </label>
                <?php wp_nonce_field('client_update_action', 'client_update_nonce'); ?>
                <button type="submit" 
                        style="background:#0073aa;color:#fff;border:none;padding:10px 15px;border-radius:5px;cursor:pointer;">
                    Save Changes
                </button>
            </form>
        </section>
        <?php endif; ?>

    </article>

</main>

<?php
    endwhile;
else :
    echo '<p style="text-align:center;margin-top:40px;">Client not found.</p>';
endif;

get_footer();
