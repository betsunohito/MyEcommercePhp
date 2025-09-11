</div>
<!-- main-panel ends -->    
</div>
<!-- page-body-wrapper ends -->
</div>
<!-- container-scroller -->

<!-- Page-Specific JS -->
<?php if (isset($page_js) && is_array($page_js)): ?>
  <?php foreach ($page_js as $js): ?>
    <script src="<?= BASE_PATH ?>/js/<?php echo $js; ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>

<!-- plugins:js -->
<script src="<?= BASE_PATH ?>/vendors/base/vendor.bundle.base.js"></script>
<!-- endinject -->

<!-- Plugin js for this page-->
<script src="<?= BASE_PATH ?>/vendors/chart.js/Chart.min.js"></script>
<script src="<?= BASE_PATH ?>/js/jquery.cookie.js" type="text/javascript"></script>
<!-- End plugin js for this page-->

<!-- inject:js -->
<script src="<?= BASE_PATH ?>/js/off-canvas.js"></script>
<script src="<?= BASE_PATH ?>/js/hoverable-collapse.js"></script>
<script src="<?= BASE_PATH ?>/js/template.js"></script>
<script src="<?= BASE_PATH ?>/js/todolist.js"></script>
<!-- endinject -->

<!-- Custom js for this page-->
<script src="<?= BASE_PATH ?>/js/dashboard.js"></script>
<!-- End custom js for this page-->
<div id="status-container" class="status-container"></div>
<div id="shipment-toast" class="toast-notification"></div>
<div id="simulate-hover-popup" class="hover-popup" style="display: none;"></div>


</body>
</html>
