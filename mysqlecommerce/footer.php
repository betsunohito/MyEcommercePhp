<!-- Page-Specific JS -->
<?php if (isset($page_js) && is_array($page_js)): ?>
  <?php foreach ($page_js as $js): ?>
    <script src="<?php echo BASE_PATH . '/js/' . $js; ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>


<div id="previewModal" class="modal">
  <div class="modal-content">
    <span class="close-btn">&times;</span>
    <div id="modalBody"></div>
  </div>
</div>


<div id="loading-overlay" style="display: none;">
  <div class="loading-message">Loading...</div>
</div>



<!-- Footer -->
<footer class="footer-text-center footer-text-lg-start bg-body-tertiary footer-text-muted">
  <!-- Section: Social media -->
  <section
    class="footer-d-flex footer-justify-content-center footer-justify-content-lg-between footer-p-4 footer-border-bottom">
    <!-- Left -->
    <div class="footer-me-5 footer-d-none footer-d-lg-block">
      <span>Get connected with us on social networks:</span>
    </div>
    <!-- Left -->

    <!-- Right -->
    <div>
      <a href="https://github.com/betsunohito" class="footer-me-4 footer-text-reset">
        <i>github</i>
      </a>
      <a href="https://www.linkedin.com/in/tarkan-kaya/" class="footer-me-4 footer-text-reset">
        <i>Linkedin</i>
      </a>
    </div>
    <!-- Right -->
  </section>
  <!-- Section: Social media -->

  <!-- Section: Links  -->
  <div>
    <div class="footer-container footer-text-center footer-text-md-start footer-mt-5">
      <!-- Grid row -->
      <div class="footer-row footer-mt-3">
        <!-- Grid column -->
        <div class="footer-col footer-col-md-3 footer-col-lg-4 footer-col-xl-3 footer-mx-auto footer-mb-4">
          <!-- Content -->
          <h6 class="footer-text-uppercase footer-fw-bold footer-mb-4">
            <i class="fas fa-gem footer-me-3"></i>Modaway
          </h6>
          <p>
            Kullanıcı dostu arayüzü ve güçlü altyapısıyla modern bir e-ticaret platformudur.
            Moda, elektronik ve daha birçok kategoride alışveriş yapmayı kolaylaştırır.
            Güvenli ödeme seçenekleri ve hızlı kargo ile müşterilere en iyi deneyimi sunmayı hedefler.
          </p>
        </div>

        <!-- Grid column -->

        <!-- Grid column -->
        <div class="footer-col footer-col-md-2 footer-col-lg-2 footer-col-xl-2 footer-mx-auto footer-mb-4">
          <!-- Links -->
          <h6 class="footer-text-uppercase footer-fw-bold footer-mb-4">
            Projelerim
          </h6>
          <p>
            <a href="https://github.com/betsunohito/SameFileFinder" class="footer-text-reset">Same File Finder</a>
          </p>
          <p>
            <a href="https://github.com/betsunohito/myShortcuts" class="footer-text-reset">My Shortcuts</a>
          </p>
          <p>
            <a href="https://github.com/betsunohito/CoverageCleaner" class="footer-text-reset">Coverage Cleaner</a>
          </p>
        </div>
        <!-- Grid column -->

        <!-- Grid column -->
        <div class="footer-col footer-col-md-3 footer-col-lg-2 footer-col-xl-2 footer-mx-auto footer-mb-4">
          <!-- Links -->
          <h6 class="footer-text-uppercase footer-fw-bold footer-mb-4">
            Useful links
          </h6>
          <p>
            <a href="#!" class="footer-text-reset">Pricing</a>
          </p>
          <p>
            <a href="#!" class="footer-text-reset">Settings</a>
          </p>
          <p>
            <a href="#!" class="footer-text-reset">Orders</a>
          </p>
          <p>
            <a href="#!" class="footer-text-reset">Help</a>
          </p>
        </div>
        <!-- Grid column -->

        <!-- Grid column -->
        <div
          class="footer-col footer-col-md-4 footer-col-lg-3 footer-col-xl-3 footer-mx-auto footer-mb-md-0 footer-mb-4">
          <!-- Links -->
          <h6 class="footer-text-uppercase footer-fw-bold footer-mb-4">Contact</h6>
          <p><i class="fas fa-envelope footer-me-3"></i>tarkan_kaya@ymail.com</p>
        </div>
        <!-- Grid column -->
      </div>
      <!-- Grid row -->
    </div>
  </div>
  <!-- Section: Links  -->

  <!-- Copyright -->
  <div class="footer-text-center footer-p-4 line-bottom-footer">
    <a class="footer-text-reset footer-fw-bold" href="https://github.com/betsunohito">github.com/betsunohito</a>
  </div>
  <!-- Copyright -->
</footer>
<div id="status-container" class="status-container"></div>


</body>

</html>