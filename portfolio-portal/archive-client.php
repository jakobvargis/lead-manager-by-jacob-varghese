<?php
/**
 * Template for displaying all Clients (with search + pagination)
 */

get_header();

// Handle search query
$search_query = isset($_GET['client_search']) ? sanitize_text_field($_GET['client_search']) : '';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Custom query for Clients
$args = array(
    'post_type'      => 'client',
    'posts_per_page' => 6,
    'paged'          => $paged,
);

if (!empty($search_query)) {
    $args['s'] = $search_query;
}

$query = new WP_Query($args);
?>

<main class="clients-archive container" style="max-width:1000px;margin:40px auto;padding:0 20px;">

    <header class="archive-header" style="text-align:center;margin-bottom:30px;">
        <h1 style="font-size:2em;margin-bottom:10px;">Our Clients</h1>
        <p style="color:#555;">Converted from leads and proudly served.</p>
    </header>

    <!-- Search Form -->
    <form method="get" action="<?php echo esc_url(get_post_type_archive_link('client')); ?>" 
          style="text-align:center;margin-bottom:30px;">
        <input type="text" name="client_search" placeholder="Search by name, email..." 
               value="<?php echo esc_attr($search_query); ?>"
               style="padding:8px 12px;width:60%;max-width:400px;border:1px solid #ccc;border-radius:6px;">
        <button type="submit" 
                style="padding:8px 14px;border:none;background:#0073aa;color:#fff;border-radius:6px;cursor:pointer;">
            Search
        </button>
        <?php if (!empty($search_query)): ?>
            <a href="<?php echo esc_url(get_post_type_archive_link('client')); ?>" 
               style="margin-left:10px;color:#0073aa;text-decoration:none;">Clear</a>
        <?php endif; ?>
    </form>

    <?php if ($query->have_posts()) : ?>
        <div class="client-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php
                $email = get_post_meta(get_the_ID(), '_pp_email', true);
                $phone = get_post_meta(get_the_ID(), '_pp_phone', true);
                ?>
                <article class="client-card" style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:20px;box-shadow:0 2px 5px rgba(0,0,0,0.08);transition:transform .2s ease;">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="client-thumb" style="text-align:center;margin-bottom:15px;">
                            <?php the_post_thumbnail('medium', ['style'=>'border-radius:10px;width:100%;height:auto;']); ?>
                        </div>
                    <?php endif; ?>

                    <h2 style="margin-bottom:10px;font-size:1.4em;">
                        <a href="<?php the_permalink(); ?>" style="text-decoration:none;color:#0073aa;"><?php the_title(); ?></a>
                    </h2>

                    <ul style="list-style:none;padding:0;font-size:0.95em;color:#333;">
                        <?php if ($email): ?>
                            <li><strong>Email:</strong> <?php echo esc_html($email); ?></li>
                        <?php endif; ?>
                        <?php if ($phone): ?>
                            <li><strong>Phone:</strong> <?php echo esc_html($phone); ?></li>
                        <?php endif; ?>
                    </ul>

                    <div style="margin-top:10px;font-size:0.9em;color:#555;">
                        <?php echo wp_trim_words(get_the_content(), 20, '…'); ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination" style="text-align:center;margin:40px 0;">
            <?php
            echo paginate_links(array(
                'total'   => $query->max_num_pages,
                'current' => $paged,
                'prev_text' => '← Prev',
                'next_text' => 'Next →',
            ));
            ?>
        </div>

    <?php else : ?>
        <p style="text-align:center;margin-top:60px;color:#777;font-size:1.1em;">No clients found.</p>
    <?php endif; ?>

</main>

<?php
wp_reset_postdata();
get_footer();
