<?php
/**
 * Template Name: Edit Leads (Frontend)
 * Description: A frontend interface for admins to view, edit, convert, and delete leads.
 */

if (!defined('ABSPATH')) exit;
get_header();

// Restrict to admins
if (!current_user_can('manage_options')) {
    echo '<p style="text-align:center;margin:40px auto;">Sorry, you are not allowed to access this page.</p>';
    get_footer();
    exit;
}

// -----------------------------
// Handle Lead Update
// -----------------------------
if (isset($_POST['lead_update_nonce']) && wp_verify_nonce($_POST['lead_update_nonce'], 'lead_update_action')) {
    $lead_id = intval($_POST['lead_id']);
    if ($lead_id && current_user_can('edit_post', $lead_id)) {
        $new_email  = sanitize_email($_POST['lead_email']);
        $new_phone  = sanitize_text_field($_POST['lead_phone']);
        $new_status = sanitize_text_field($_POST['lead_status']);
        update_post_meta($lead_id, '_pp_email', $new_email);
        update_post_meta($lead_id, '_pp_phone', $new_phone);
        update_post_meta($lead_id, '_pp_status', $new_status);
        echo '<div style="max-width:800px;margin:20px auto;padding:10px;background:#dff0d8;color:#3c763d;border-radius:6px;text-align:center;">✅ Lead updated successfully!</div>';
    }
}

// -----------------------------
// Handle Lead → Client Conversion
// -----------------------------
if (isset($_GET['convert_lead']) && wp_verify_nonce($_GET['_wpnonce'], 'convert_lead_' . $_GET['convert_lead'])) {
    $lead_id = intval($_GET['convert_lead']);
    if ($lead_id && current_user_can('manage_options')) {
        $lead = get_post($lead_id);
        if ($lead && $lead->post_type === 'lead') {
            $already_converted = get_post_meta($lead_id, '_pp_converted', true);
            if ($already_converted) {
                echo '<div style="max-width:800px;margin:20px auto;padding:10px;background:#fff3cd;color:#856404;border-radius:6px;text-align:center;">⚠️ This lead is already converted to Client ID #' . esc_html($already_converted) . '.</div>';
            } else {
                $email = get_post_meta($lead_id, '_pp_email', true);
                $phone = get_post_meta($lead_id, '_pp_phone', true);
                $client_id = wp_insert_post([
                    'post_type'   => 'client',
                    'post_status' => 'publish',
                    'post_title'  => $lead->post_title,
                    'post_content'=> $lead->post_content,
                ]);
                if (!is_wp_error($client_id)) {
                    update_post_meta($client_id, '_pp_email', $email);
                    update_post_meta($client_id, '_pp_phone', $phone);
                    update_post_meta($lead_id, '_pp_status', 'converted');
                    update_post_meta($lead_id, '_pp_converted', $client_id);
                    echo '<div style="max-width:800px;margin:20px auto;padding:10px;background:#d4edda;color:#155724;border-radius:6px;text-align:center;">✅ Lead converted successfully to <a href="' . esc_url(get_permalink($client_id)) . '">Client #' . esc_html($client_id) . '</a></div>';
                }
            }
        }
    }
}

// -----------------------------
// Handle Lead Deletion
// -----------------------------
if (isset($_GET['delete_lead']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_lead_' . $_GET['delete_lead'])) {
    $lead_id = intval($_GET['delete_lead']);
    if ($lead_id && current_user_can('delete_post', $lead_id)) {
        wp_delete_post($lead_id, true);
        echo '<div style="max-width:800px;margin:20px auto;padding:10px;background:#f8d7da;color:#721c24;border-radius:6px;text-align:center;">🗑️ Lead deleted successfully!</div>';
    }
}

// Determine selected lead
$selected_lead = isset($_GET['lead_id']) ? intval($_GET['lead_id']) : 0;
?>

<main class="lead-admin container" style="max-width:1000px;margin:40px auto;padding:0 20px;">

    <h1 style="text-align:center;margin-bottom:30px;">Manage Leads (Frontend)</h1>

    <?php
    // If a lead is selected → show edit form
    if ($selected_lead) :
        $lead = get_post($selected_lead);
        if ($lead && $lead->post_type === 'lead'):
            $email  = get_post_meta($selected_lead, '_pp_email', true);
            $phone  = get_post_meta($selected_lead, '_pp_phone', true);
            $status = get_post_meta($selected_lead, '_pp_status', true);
    ?>
        <article style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:25px;box-shadow:0 3px 8px rgba(0,0,0,0.08);">
            <h2 style="margin-bottom:15px;">✏️ Edit Lead: <?php echo esc_html($lead->post_title); ?></h2>
            <form method="post" style="display:flex;flex-direction:column;gap:12px;max-width:400px;">
                <input type="hidden" name="lead_id" value="<?php echo esc_attr($selected_lead); ?>">
                <label>Email:
                    <input type="email" name="lead_email" value="<?php echo esc_attr($email); ?>" required 
                           style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
                </label>
                <label>Phone:
                    <input type="text" name="lead_phone" value="<?php echo esc_attr($phone); ?>" 
                           style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
                </label>
                <label>Status:
                    <select name="lead_status" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
                        <?php
                        $statuses = ['new' => 'New', 'contacted' => 'Contacted', 'converted' => 'Converted', 'closed' => 'Closed'];
                        foreach ($statuses as $key => $label) {
                            printf('<option value="%s"%s>%s</option>', esc_attr($key), selected($status, $key, false), esc_html($label));
                        }
                        ?>
                    </select>
                </label>
                <?php wp_nonce_field('lead_update_action', 'lead_update_nonce'); ?>
                <button type="submit" style="background:#0073aa;color:#fff;border:none;padding:10px 15px;border-radius:5px;cursor:pointer;">
                    Save Changes
                </button>
                <a href="<?php echo esc_url(get_permalink()); ?>" style="margin-top:10px;color:#0073aa;text-decoration:none;">← Back to All Leads</a>
            </form>
        </article>
    <?php
        else:
            echo '<p style="text-align:center;">Invalid lead.</p>';
        endif;
    else:
        // Otherwise show the leads list
        $leads = get_posts(['post_type'=>'lead','numberposts'=>50,'post_status'=>'publish']);
        if ($leads):
    ?>
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="border:1px solid #ddd;padding:8px;">Name</th>
                    <th style="border:1px solid #ddd;padding:8px;">Email</th>
                    <th style="border:1px solid #ddd;padding:8px;">Phone</th>
                    <th style="border:1px solid #ddd;padding:8px;">Status</th>
                    <th style="border:1px solid #ddd;padding:8px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $lead): ?>
                    <?php
                        $status = get_post_meta($lead->ID, '_pp_status', true);
                        $converted = get_post_meta($lead->ID, '_pp_converted', true);
                        $edit_url = add_query_arg('lead_id', $lead->ID, get_permalink());
                        $convert_url = wp_nonce_url(add_query_arg('convert_lead', $lead->ID, get_permalink()), 'convert_lead_' . $lead->ID);
                        $delete_url = wp_nonce_url(add_query_arg('delete_lead', $lead->ID, get_permalink()), 'delete_lead_' . $lead->ID);
                    ?>
                    <tr>
                        <td style="border:1px solid #ddd;padding:8px;"><?php echo esc_html($lead->post_title); ?></td>
                        <td style="border:1px solid #ddd;padding:8px;"><?php echo esc_html(get_post_meta($lead->ID, '_pp_email', true)); ?></td>
                        <td style="border:1px solid #ddd;padding:8px;"><?php echo esc_html(get_post_meta($lead->ID, '_pp_phone', true)); ?></td>
                        <td style="border:1px solid #ddd;padding:8px;"><?php echo esc_html(ucfirst($status)); ?></td>
                        <td style="border:1px solid #ddd;padding:8px;white-space:nowrap;">
                            <a href="<?php echo esc_url($edit_url); ?>" style="color:#0073aa;text-decoration:none;margin-right:10px;">✏️ Edit</a>
                            <?php if (!$converted): ?>
                                <a href="<?php echo esc_url($convert_url); ?>" style="color:green;text-decoration:none;margin-right:10px;">🔄 Convert</a>
                            <?php else: ?>
                                <span style="color:#555;">✅ Converted</span>
                            <?php endif; ?>
                            <a href="<?php echo esc_url($delete_url); ?>" style="color:red;text-decoration:none;"
                               onclick="return confirm('Are you sure you want to delete this lead? This action cannot be undone.');">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php
        else:
            echo '<p style="text-align:center;">No leads found.</p>';
        endif;
    endif;
    ?>
</main>

<?php get_footer(); ?>
