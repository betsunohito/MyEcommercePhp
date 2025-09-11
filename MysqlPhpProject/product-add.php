<?php
session_start();

// Check if admin is logged in by verifying the session variable
if (!isset($_SESSION['admin_id'])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
$page_css = ["product_add.css"];
$page_js = ['product-add.js'];
include 'header.php';
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Product Add</h4>
                    <p class="card-description">
                        This is where you can add a new product to the inventory.
                    </p>
                    <form id="productAddForm" class="forms-sample" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="productBarcode">Barcode ID</label>
                            <input type="text" class="form-control" placeholder="Enter barcode ID" name="productBarcode"
                                id="productBarcode">
                        </div>

                        <div class="form-group">
                            <label for="productTitle">Title Of Your Product</label>
                            <input type="text" class="form-control" placeholder="title" name="productTitle"
                                id="productTitle">
                        </div>
                        <div class="form-group">
                            <label for="productDescription">Description</label>
                            <textarea class="form-control" id="productDescription" name="productDescription" rows="4"
                                placeholder="Enter product description..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="productCategory">Category</label>
                            <select id="productCategory" name="productCategory" class="form-control-custom">
                                <option value="">-- Select Category --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="productSubCategory">Subcategory</label>
                            <select id="productSubCategory" name="productSubCategory" class="form-control-custom">
                                <option value="">-- Select Subcategory --</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="productTetCategory">Tetcategory</label>
                            <select id="productTetCategory" name="productTetCategory" class="form-control-custom">
                                <option value="">-- Select Tetcategory --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="productBrand">Brand</label>
                            <select id="productBrand" name="productBrand" class="form-control-custom">
                                <option value="">-- Select Brand --</option>
                            </select>
                        </div>






                        <div class="form-group image-upload-group">
                            <label>Upload up to 4 images</label>
                            <div class="upload-wrapper">
                                <div class="upload-controls">
                                    <input type="file" name="img[]" id="file-upload" class="file-upload-input" multiple
                                        accept="image/*">
                                    <button type="button" class="btn btn-primary file-upload-button">Choose
                                        Images</button>
                                    <p class="file-upload-text">Max 4 images</p>
                                </div>
                                <div class="upload-preview" id="preview-images"></div>
                            </div>
                        </div>



                        <div class="button-group-center">
                            <button type="submit" class="btn btn-primary me-2">Submit</button>
                            <button class="btn btn-light">Cancel</button>
                        </div>
                    </form>

                    <div id="formMessage" style="display:none;" class="alert"></div>
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

    <?php include 'footer.php'; ?>