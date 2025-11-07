<?php
/**
 * Front Page Template for Portfolio Portal Theme
 * Author: Jakob Varghese
 */
get_header();
?>

<main class="site-main">

  <!-- 🟦 HERO SECTION -->
  <section class="hero">
    <div class="hero-content">
      <h1>Hi, I’m <strong>Jakob Varghese</strong></h1>
      <h2>WordPress Developer &amp; Tech Enthusiast</h2>
      <p>
        This is <strong>Portfolio Portal</strong> — a custom WordPress theme + plugin demo built from scratch to showcase advanced WP development, AJAX, REST API, and clean UI design.
      </p>
      <div class="hero-buttons">
        <a href="<?php echo esc_url( site_url('/submit-lead/') ); ?>" class="btn-primary">Try the Lead Form</a>
        <a href="<?php echo esc_url( site_url('/clients/') ); ?>" class="btn-outline">View Clients</a>
      </div>
    </div>
  </section>

  <!-- 🧩 PROJECT HIGHLIGHTS -->
  <section class="features">
    <h2>Project Highlights</h2>
    <ul>
      <li>Custom Post Types: <strong>Clients</strong> &amp; <strong>Leads</strong></li>
      <li>Custom Plugin with <strong>AJAX Lead Form</strong> and <strong>REST API</strong></li>
      <li>Dashboard Widget &amp; Admin Meta Boxes</li>
      <li>Responsive Design with Semantic HTML</li>
    </ul>
  </section>

  <!-- 📊 LIVE STATS -->
  <section class="stats">
    <h2>Quick Stats</h2>
    <?php
      $lead_count   = wp_count_posts('lead')->publish ?? 0;
      $client_count = wp_count_posts('client')->publish ?? 0;
    ?>
    <div class="stat-boxes">
      <div class="stat">
        <strong><?php echo esc_html( $lead_count ); ?></strong>
        <span>Leads Submitted</span>
      </div>
      <div class="stat">
        <strong><?php echo esc_html( $client_count ); ?></strong>
        <span>Clients Added</span>
      </div>
    </div>
  </section>

</main>

<style>
  /* Global layout */
  .site-main {
    font-family: "Segoe UI", system-ui, sans-serif;
    color: #222;
    margin: 0 auto;
    padding: 0;
  }

  /* HERO SECTION */
  .hero {
    background: linear-gradient(120deg, #0073aa, #005f8d);
    color: #fff;
    padding: 100px 20px;
    text-align: center;
  }
  .hero-content {
    max-width: 800px;
    margin: 0 auto;
  }
  .hero h1 {
    font-size: 2.4em;
    margin-bottom: 0.3em;
  }
  .hero h2 {
    font-size: 1.4em;
    font-weight: 400;
    color: #cce7ff;
    margin-bottom: 1em;
  }
  .hero p {
    font-size: 1.1em;
    line-height: 1.6;
    margin-bottom: 1.5em;
  }

  /* Buttons */
  .btn-primary,
  .btn-outline {
    display: inline-block;
    padding: 12px 22px;
    margin: 0.4em;
    border-radius: 6px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.25s ease;
  }
  .btn-primary {
    background: #fff;
    color: #0073aa;
  }
  .btn-primary:hover {
    background: #f0f8ff;
  }
  .btn-outline {
    border: 2px solid #fff;
    color: #fff;
  }
  .btn-outline:hover {
    background: #fff;
    color: #0073aa;
  }

  /* FEATURES */
  .features {
    max-width: 800px;
    margin: 60px auto;
    padding: 0 20px;
  }
  .features h2 {
    text-align: center;
    margin-bottom: 20px;
  }
  .features ul {
    list-style: disc;
    margin-left: 2em;
    line-height: 1.8;
  }

  /* STATS */
  .stats {
    background: #f7f7f7;
    padding: 60px 20px;
    text-align: center;
  }
  .stat-boxes {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-top: 20px;
    flex-wrap: wrap;
  }
  .stat {
    background: #fff;
    padding: 20px 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  .stat strong {
    display: block;
    font-size: 2em;
    color: #0073aa;
  }
  .stat span {
    color: #555;
  }

  /* FOOTER */
  .home-footer {
    padding: 30px;
    text-align: center;
    font-size: 0.9em;
    color: #777;
    border-top: 1px solid #ddd;
    margin-top: 40px;
  }
</style>

<?php get_footer(); ?>
