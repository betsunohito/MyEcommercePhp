<?php
session_start();

// Check if admin is logged in by verifying the session variable
if (!isset($_SESSION['admin_id'])) {
  // Not logged in, redirect to login page
  header("Location: login.php");
  exit();
}
$page_css = ['footer.css', "category.css"];
$page_js = ['toast.js', 'category.js'];
// If logged in, you can safely include your header and rest of the page
include 'header.php';
?>
<div class="content-wrapper">
  <div class="row">
    <div class="product-search-container">
      <input type="text" id="product_category" class="product-search-box" placeholder="Search for a product...">

      <div class="product-list">



        <!-- Repeat .product-item as needed -->
      </div>
    </div>
  </div>
  <div class="row">

    <!-- CATEGORY 1 CARD -->
    <div class="col-md-3 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="category-name mb-2">Category</div>
          <div class="autocomplete-wrapper category1-wrapper">
            <input type="text" id="category1-input" class="category1-input autocomplete-input form-control"
              placeholder="Type or select Category 1" aria-label="Category 1" autocomplete="off" />
            <!-- This hidden field is created by JS if it doesn’t already exist -->
            <input type="hidden" id="category1-input_id" class="category1-hidden-id" />

            <div class="category1-suggestions suggestions"></div>
          </div>
        </div>
      </div>
    </div>
    <!-- CATEGORY 2 CARD -->
    <div class="col-md-3 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="category-name mb-2">Sub-Category</div>
          <div class="autocomplete-wrapper category2-wrapper">
            <input type="text" id="category2-input" class="category2-input autocomplete-input form-control"
              placeholder="Type or select Sub-Category" aria-label="Sub-Category" autocomplete="off" />
            <input type="hidden" id="category2-input_id" class="category2-hidden-id" />

            <div class="category2-suggestions suggestions"></div>
          </div>
        </div>
      </div>
    </div>
    <!-- CATEGORY 3 CARD -->
    <div class="col-md-3 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="category-name mb-2">Tertiary-Category</div>
          <div class="autocomplete-wrapper category3-wrapper">
            <input type="text" id="category3-input" class="category3-input autocomplete-input form-control"
              placeholder="Type or select Tertiary-Category" aria-label="Tertiary-Category" autocomplete="off" />
            <input type="hidden" id="category3-input_id" class="category3-hidden-id" />

            <div class="category3-suggestions suggestions"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- CATEGORY 3 CARD -->
    <div class="col-md-3 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">

          <div
            class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
            <h3 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0">Save Categories</h3>
            <!-- YOUR BUTTON (unchanged) -->
            <div id="category-save-message" class="mt-3 text-center"></div>

            <div class="add-items d-flex mb-0 mt-4">


              <button class="add btn btn-icon text-primary todo-list-add-btn bg-transparent">
                <i class="ti-location-arrow"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>



  <div class="row">

    <!-- CATEGORY CARD -->
    <div class="col-md-4 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="category-name mb-2">Category</div>
          <div class="autocomplete-wrapper category-main-wrapper">
            <input type="text" id="category-main-input" class="category-main-input autocomplete-input form-control"
              placeholder="Type or select a category" aria-label="Main Category" autocomplete="off" />

            <!-- Hidden input to store selected category ID -->
            <input type="hidden" id="category-main-input_id" class="category-main-hidden-id" />

            <!-- Suggestions will be injected here -->
            <div class="category-main-suggestions suggestions"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Brand CARD -->
    <div class="col-md-4 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="brand-name mb-2">Brand</div>
          <div class="autocomplete-wrapper brand-wrapper">
            <input type="text" id="brand-input" class="brand-input autocomplete-input form-control" placeholder="Type or select a brand"
              aria-label="Brand" autocomplete="off" />

            <!-- Hidden input for selected brand ID -->
            <input type="hidden" id="brand_name" class="brand-hidden-id" />
            <div id="brand-response" class="alert mt-3 d-none"></div>

            <div class="brand-suggestions suggestions"></div>
          </div>

        </div>
      </div>
    </div>

    <div class="col-md-4 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div
            class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
            <h3 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0">Add brand and connect to category</h3>

            <div class="add-items d-flex mb-0 mt-4">
              <button type="button" class="add btn btn-icon text-primary todo-list-add-btn bg-transparent brand-add-btn"
                title="Add brand and link to category">
                <i class="ti-location-arrow"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="row">
      <!-- Type Category Selection -->
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="category-name mb-2">Type Category</div>
            <div class="autocomplete-wrapper type-category-wrapper">
              <input type="text" id="type-category-input" class="type-category-input autocomplete-input form-control"
                placeholder="Type or select a category" aria-label="Category for type" autocomplete="off" />

              <!-- Hidden input to store selected category ID -->
              <input type="hidden" id="type-category-input_id" class="type-category-hidden-id" />

              <!-- Suggestions will be injected here -->
              <div class="type-category-suggestions suggestions"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Type Name Input (replaces Brand) -->
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="brand-name mb-2">Product Type Name</div>
            <div class="autocomplete-wrapper type-wrapper">
              <input type="text" id="type-input" class="type-input autocomplete-input form-control"
                placeholder="Enter product type (e.g. S, M, 32GB)" aria-label="Product Type" autocomplete="off" />

              <!-- Hidden input for selected type ID (optional if needed) -->
              <input type="hidden" id="type_name" class="type-hidden-id" />
              <div id="type-response" class="alert mt-3 d-none"></div>

              <div class="type-suggestions suggestions"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Button -->
      <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div
              class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
              <h3 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0">Add Product Type and connect to category</h3>

              <div class="add-items d-flex mb-0 mt-4">
                <button type="button"
                  class="add btn btn-icon text-primary todo-list-add-btn bg-transparent type-add-btn"
                  title="Add type and link to category">
                  <i class="ti-location-arrow"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>




</div>
<!-- content-wrapper ends -->
<!-- partial:partials/_footer.html -->
<footer class="footer">
  <div class="d-sm-flex justify-content-center justify-content-sm-between">
    <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © <a
        href="https://www.bootstrapdash.com/" target="_blank">bootstrapdash.com </a>2021</span>
    <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Only the best <a
        href="https://www.bootstrapdash.com/" target="_blank"> Bootstrap dashboard </a> templates</span>
  </div>
</footer>
<!-- partial -->
<!-- put this near the end of your <body> -->
<div id="notification-container"></div>
<?php
include 'footer.php'; ?>