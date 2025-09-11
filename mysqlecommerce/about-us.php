<?php $page_css = ["about-us.css"];
include 'header.php';
?>

<section class="about-hero">
    <h1>About Us</h1>
    <p>We are passionate about delivering quality products and a smooth shopping experience for everyone.</p>
</section>

<!-- Content Sections -->
<div class="about-section">

    <div class="about-card">
        <h2>Our Story</h2>
        <p>
            Founded in <?php echo date('Y') - 3; ?>, our journey began as a small local store.
            Today, we proudly serve customers nationwide, offering top-quality products,
            competitive prices, and reliable service.
        </p>
    </div>

    <div class="about-card">
        <h2>Our Values</h2>
        <ul class="values-list">
            <li>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span><strong>Customer First:</strong> Your satisfaction is our top priority.</span>
            </li>
            <li>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M9 21V3m6 18V3" />
                </svg>
                <span><strong>Quality Products:</strong> We partner with trusted suppliers only.</span>
            </li>
            <li>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3" />
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        d="M19.4 15a1.65 1.65 0 0 0 0 2.3l.7.7a2 2 0 0 1-2.8 2.8l-.7-.7a1.65 1.65 0 0 0-2.3 0l-.7.7a2 2 0 0 1-2.8-2.8l.7-.7a1.65 1.65 0 0 0 0-2.3l-.7-.7a2 2 0 0 1 2.8-2.8l.7.7a1.65 1.65 0 0 0 2.3 0l.7-.7a2 2 0 0 1 2.8 2.8l-.7.7z" />
                </svg>
                <span><strong>Transparency:</strong> We believe in honest pricing & policies.</span>
            </li>
            <li>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect width="20" height="14" x="2" y="5" rx="2" ry="2" />
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M2 10h20" />
                </svg>
                <span><strong>Security:</strong> We protect your data with the latest technology.</span>
            </li>
        </ul>
    </div>

    <div class="about-card contact-info">
        <h2>Contact Us</h2>
        <p>
            📧 Email: <a href="mailto:support@example.com">support@example.com</a><br>
            📞 Phone: +90 555 123 4567
        </p>
    </div>

</div>

<?php include 'footer.php'; // optional if you have a shared footer ?>