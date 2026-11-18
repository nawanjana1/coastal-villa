<?php
session_start();

function escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Relax by Sri Lanka's southern coast at Coastal Villa. Browse our rooms and plan your stay.">
    <title>Coastal Villa | Stay by the Sea</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body id="top">
    <?php
    $basePath = '';
    $adminArea = false;
    require __DIR__ . '/includes/navigation.php';
    ?>

    <main>
        <section class="hero">
            <div class="hero-content">
                <p class="hero-label">A relaxing coastal stay · Southern Sri Lanka</p>
                <h1>Slow mornings.<br>Sea air. Your stay.</h1>
                <p>Find a welcoming room, unwind among the palms and make time for the coast.</p>
                <a class="button-link" href="availability.php">Book your stay</a>
            </div>
        </section>

        <section class="page-shell intro-section reveal">
            <div class="intro-copy">
                <p class="hero-label">Welcome to Coastal Villa</p>
                <h2>A calm place to land by the sea</h2>
                <p>Settle into a comfortable room, enjoy thoughtful facilities and explore the southern coast at your own pace. Browse the rooms and send a booking request in just a few steps.</p>
                <a class="card-link" href="rooms.php">Explore the rooms</a>
            </div>
            <img class="intro-image" src="assets/images/ocean-deluxe.jpg" alt="Bright coastal room looking out toward the ocean" loading="lazy">
        </section>

        <section class="page-shell reveal">
            <div class="page-heading">
                <p class="hero-label">A little comfort goes a long way</p>
                <h2>Make yourself at home</h2>
                <p>Simple comforts for an easy coastal getaway.</p>
            </div>
            <div class="feature-grid">
                <article class="feature-card reveal">
                    <span class="feature-icon" aria-hidden="true">🌊</span>
                    <h3>Coastal location</h3>
                    <p>Stay close to the beaches and relaxed pace of Sri Lanka's southern coast.</p>
                </article>
                <article class="feature-card reveal">
                    <span class="feature-icon" aria-hidden="true">🌿</span>
                    <h3>Thoughtful comforts</h3>
                    <p>Choose a room with the facilities and space that fit your trip.</p>
                </article>
                <article class="feature-card reveal">
                    <span class="feature-icon" aria-hidden="true">☀️</span>
                    <h3>Easy booking</h3>
                    <p>Search your dates, compare rooms and send a booking request online.</p>
                </article>
            </div>
        </section>
    </main>

    <footer class="site-footer" id="contact">
        <div class="footer-inner">
            <div class="footer-brand">
                <a class="footer-logo" href="#top">Coastal Villa</a>
                <p>A relaxed stay along Sri Lanka's southern coast.</p>
            </div>
            <div class="footer-contact">
                <h2>Get in touch</h2>
                <p><span>Location</span> <a href="https://maps.app.goo.gl/1MiK1qoWDwRuJEby9" target="_blank" rel="noopener noreferrer">Coastal Villa, Sri Lanka</a></p>
                <p><span>Email</span> <a href="mailto:hello@coastalvilla.test">hello@coastalvilla.test</a></p>
                <p><span>Phone</span> +94 115884987</p>
                
            </div>
        </div>
        <div class="footer-bottom">
            <p>© <?= date('Y') ?> Coastal Villa. All rights reserved.</p>
            <a href="#top">Back to top ↑</a>
        </div>
    </footer>
</body>
</html>


