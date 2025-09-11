<?php
$page_css = ["checkout.css"];
$page_js = ["toast.js", "checkout.js"];
include 'header.php';
?>

<!-- Checkout Section Begin -->
<section class="checkout">
    <div class="container-checkout">
        <div class="checkout__form">
            <div class="row">
                <div class="col-lg-8 col-md-6 tab-content active">
                    <h3>
                        Teslimat Adresi
                        <button class="new-addres-add" onclick="openModalAddressAdd()">Add New Address</button>
                    </h3>
                    <div class="tabadress1 tab-header" id="address-Container">

                        <?php
                        define('INCLUDE_MODE', true);
                        $addresses = include 'tools/action/address_show_selected.php';
                        $delivery_addresses = $addresses['delivery_address'] ?? [];
                        $billing_addresses = $addresses['billing_address'] ?? [];
                        ?>

                        <!-- Delivery -->
                        <div class="delivery-address">
                            <div class="tab-header">
                                <h5>Delivery address </h5>
                                <h6>
                                    <div class="change-address" id="changeAddressBtn">Change Address</div>
                                </h6>
                            </div>
                            <div>
                                <div id="delivery-address-of-user">
                                    <?php if ($delivery_addresses && count($delivery_addresses) > 0): ?>
                                        <?php foreach ($delivery_addresses as $address):
                                            $restrictedNote = trim($address['user_address_note']);
                                            $restrictedNote = preg_replace("/[,\.]/", '', $restrictedNote);
                                            if (strlen($restrictedNote) > 35) {
                                                $restrictedNote = substr($restrictedNote, 0, 40) . "...";
                                            }
                                            ?>
                                            <div class="card-wrapper">
                                                <div class="card add-btn" data-id="<?= $address['user_address_id'] ?>"
                                                    id="my-card-<?= $address['user_address_id'] ?>">
                                                    <div class="edit-text">
                                                        <span
                                                            onclick="editAddress(<?= $address['user_address_id'] ?>)">Edit</span>
                                                    </div>
                                                    <div class="background-shape"></div>
                                                    <div class="phone-number">
                                                        <?= htmlspecialchars($address['user_address_phone']) ?>
                                                    </div>
                                                    <div class="content">
                                                        <h2><?= htmlspecialchars($address['user_firstname'] . " " . $address['user_lastname']) ?>
                                                        </h2>
                                                        <p><?= htmlspecialchars($restrictedNote) ?></p>
                                                        <p><?= htmlspecialchars($address['province_name'] . " / " . $address['district_name'] . " / " . $address['neighborhood_name']) ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="addaddresscardcase" id="addressContainer">
                                            <button class="addaddresscard" onclick="openModalAddressAdd()">
                                                <span>+</span> Add new Address
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($billing_addresses && count($billing_addresses) > 0): ?>
                                    <!-- Case 1: billing addresses exist -->
                                    <div class="checkout__input__checkbox sameforboth-div" id="sameforbothToggle">
                                        <div class="css-checkbox">
                                            <span class="box"></span>
                                            <span class="label-text">The delivery address and the billing address are the
                                                same.</span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Case 2: no billing addresses, auto mark as checked -->
                                    <div class="checkout__input__checkbox sameforboth-div checked" id="sameforbothToggle">
                                        <div class="css-checkbox">
                                            <span class="box"></span>
                                            <span class="label-text">The delivery address and the billing address are the
                                                same.</span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Billing -->
                        <div class="billing-address" id="billing-address">
                            <div class="tab-header" <?php if (empty($billing_addresses))
                                echo 'style="display:none;"'; ?>>
                                <h5>Billing address</h5>
                                <h6>
                                    <div class="change-address" id="changeBillingBtn">Change Address</div>
                                </h6>
                            </div>

                            <div id="billing-address-of-user">
                                <?php if (!empty($billing_addresses)): ?>
                                    <?php foreach ($billing_addresses as $address):
                                        $restrictedNote = trim($address['user_address_note']);
                                        $restrictedNote = preg_replace("/[,\.]/", '', $restrictedNote);
                                        if (strlen($restrictedNote) > 35) {
                                            $restrictedNote = substr($restrictedNote, 0, 40) . "...";
                                        }
                                        ?>
                                        <div class="card-wrapper">
                                            <div class="card add-btn" data-id="<?= $address['user_address_id'] ?>"
                                                id="my-card-<?= $address['user_address_id'] ?>">
                                                <div class="edit-text">
                                                    <span onclick="editAddress(<?= $address['user_address_id'] ?>)">Edit</span>
                                                </div>
                                                <div class="background-shape"></div>
                                                <div class="phone-number">
                                                    <?= htmlspecialchars($address['user_address_phone']) ?>
                                                </div>
                                                <div class="content">
                                                    <h2><?= htmlspecialchars($address['user_firstname'] . " " . $address['user_lastname']) ?>
                                                    </h2>
                                                    <p><?= htmlspecialchars($restrictedNote) ?></p>
                                                    <p><?= htmlspecialchars($address['province_name'] . " / " . $address['district_name'] . " / " . $address['neighborhood_name']) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>





                    <!-- Lazy Loader Begin -->
                    <div class="page-loader" id="pageLoader">
                        <div class="loader"></div>
                    </div>
                    <!-- Lazy Loader End -->
                </div>




                <!-- Price Detail Section End -->

                <div class="col-lg-4 col-md-6 checkout__wrapper">
                    <div class="checkout__order">
                        <h4>Your Order</h4>
                        <div class="checkout__order__total">Total<span>
                                ₺<?php include 'tools/action/shoppingcart-total.php'; ?></span></div>
                        <form id="orderForm">
                            <button type="submit" class="site-btn">PLACE ORDER</button>
                        </form>

                    </div>
                </div>
                <!-- Price Detail Section End -->
                <!-- Payment Form -->
                <div class="col-lg-8 col-md-6  tab-content active">
                    <h3>Payment & Plans</h3>
                    <div class="checkout__form two-column-layout">
                        <!-- Left: Payment Form -->
                        <div class="left payment-form">
                            <p>Payment Information
                            <div id="responseMsg" tabindex="-1" class="error-message"></div>
                            </p>
                            <label for="card-number">Card Number</label>
                            <input type="text" id="card-number" name="card-number" placeholder="1234 5678 9012 3456"
                                value="1234 5678 9012 3456" maxlength="19" required />



                            <label for="card-name">Name on Card</label>
                            <input type="text" id="card-name" name="card-name" placeholder="John Doe" value="John Doe"
                                required />


                            <div class="payment-details">
                                <div>
                                    <label for="expiry">Expiration Date</label>
                                    <!-- Month Dropdown (1–12) -->
                                    <select class="small-select" name="expiry_month" id="expiry_month" required>
                                        <option value="" disabled selected>MM</option>
                                        <option value="1" selected>1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                    </select>
                                    <?php
                                    $currentYear = date('Y');
                                    ?>
                                    <!-- Year Dropdown -->
                                    <select class="small-select" name="expiry_year" id="expiry_year" required>
                                        <option value="">YY</option>
                                        <?php
                                        $chosen = $_POST['year'] ?? $_GET['year'] ?? null;
                                        for ($y = 0; $y <= 25; $y++):
                                            $year = $currentYear + $y;
                                            $yy = substr((string) $year, -2);
                                            $selected = ($chosen !== null) ? ($yy == $chosen ? ' selected' : '') : ($y === 0 ? ' selected' : '');
                                            ?>
                                            <option value="<?= $yy ?>" <?= $selected ?>><?= $yy ?></option>
                                        <?php endfor; ?>

                                    </select>
                                </div>
                                <div>
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="123" value="123" maxlength="3"
                                        required />
                                </div>
                            </div>
                        </div>

                        <!-- Right: Installment Options -->
                        <div class="right installment-options">
                            <p>Installment Options</p>
                            <form>
                                <label class="installment-option">
                                    <input type="radio" name="installment" value="1" checked />
                                    1× Full Payment – $100
                                </label>

                                <label class="installment-option">
                                    <input type="radio" name="installment" value="2" />
                                    2× Monthly Installments – $50/month
                                </label>

                                <label class="installment-option">
                                    <input type="radio" name="installment" value="3" />
                                    3× Monthly Installments – $34/month
                                </label>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Payment Form End -->
            </div>


        </div>
    </div>
</section>
<!-- Checkout Section End -->



<!-- Modal Address Section Begin -->
<div class="modal-overlay-address" onclick="closeAddressModal()"></div>
<div class="modal overlay-address" id="overlay-address">

</div>
<!-- Address Modal Section End -->

<!-- Modal Section Begin -->
<div class="modal-overlay" onclick="closeModal()"></div>
<div class="modal col-lg-8 col-md-6">

</div>

<div class="custom-modal-overlay" id="custom-modal-overlay">
    <div class="custom-modal" id="billingModalOverlay">
        <div class="custom-modal-header">
            Manage Billing Address
            <button class="custom-close-button" onclick="closeModalBillingAddress()">✖</button>
        </div>
        <div class="custom-modal-body" id="billing-modal-body">

        </div>
        <div class="custom-modal-footer">
            <button class="custom-action-button" id="BillingSaveBtn" onclick="saveAddress(this)">Use This Billing
                Address</button>
        </div>
    </div>

    <div class="custom-modal" id="customModalOverlay">
        <div class="custom-modal-header">
            Manages Delivery Address
            <button class="custom-close-button" onclick="closeModalAddress()">✖</button>
        </div>
        <div class="custom-modal-body" id="delivery-modal-body">

        </div>
        <div class="custom-modal-footer">
            <button class="custom-action-button" id="DeliverySaveBtn" onclick="saveAddress(this)">Use This Delivery
                Address</button>
        </div>
    </div>
    <div class="addressadd-modal-body" id="editAddressAddOverlay">
        <form id="editAddressForm">
            <input type="hidden" id="edit_address_id" name="edit_address_id" />
            <div class="modal-header">
                <h2>Edit Address</h2>
                <button type="button" class="close-btn-address" onclick="
    closeeditAddress()">X</button>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>Fist Name<span>*</span></p>
                        <input type="text" id="edit_first_name" name="edit_first_name" placeholder="First Name"
                            required>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>Last Name<span>*</span></p>
                        <input type="text" id="edit_last_name" name="edit_last_name" placeholder="Last Name" required>
                    </div>
                </div>
            </div>
            <div class="checkout__input__checkbox">
                <input type="checkbox" id="edit_is_company" name="edit_is_company" onchange="toggleFields()">
                <label for="edit_is_company">Kurumsal</label>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>Phone<span>*</span></p>
                        <input type="text" placeholder="0 (___) ___ __ __" maxlength="18" name="edit_phone_number"
                            id="edit_phone_number">
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>Province<span>*</span></p>
                        <div class="custom-dropdown">
                            <select id="edit_province" name="edit_province">
                                <option value="0">Please Select Province</option>
                                <option value="1">Adana</option>
                                <option value="2">Adıyaman</option>
                                <option value="3">Afyonkarahisar</option>
                                <option value="4">Ağrı</option>
                                <option value="5">Amasya</option>
                                <option value="6">Ankara</option>
                                <option value="7">Antalya</option>
                                <option value="8">Artvin</option>
                                <option value="9">Aydın</option>
                                <option value="10">Balıkesir</option>
                                <option value="11">Bilecik</option>
                                <option value="12">Bingöl</option>
                                <option value="13">Bitlis</option>
                                <option value="14">Bolu</option>
                                <option value="15">Burdur</option>
                                <option value="16">Bursa</option>
                                <option value="17">Çanakkale</option>
                                <option value="18">Çankırı</option>
                                <option value="19">Çorum</option>
                                <option value="20">Denizli</option>
                                <option value="21">Diyarbakır</option>
                                <option value="22">Edirne</option>
                                <option value="23">Elazığ</option>
                                <option value="24">Erzincan</option>
                                <option value="25">Erzurum</option>
                                <option value="26">Eskişehir</option>
                                <option value="27">Gaziantep</option>
                                <option value="28">Giresun</option>
                                <option value="29">Gümüşhane</option>
                                <option value="30">Hakkâri</option>
                                <option value="31">Hatay</option>
                                <option value="32">Isparta</option>
                                <option value="33">Mersin</option>
                                <option value="34">İstanbul</option>
                                <option value="35">İzmir</option>
                                <option value="36">Kars</option>
                                <option value="37">Kastamonu</option>
                                <option value="38">Kayseri</option>
                                <option value="39">Kırklareli</option>
                                <option value="40">Kırşehir</option>
                                <option value="41">Kocaeli</option>
                                <option value="42">Konya</option>
                                <option value="43">Kütahya</option>
                                <option value="44">Malatya</option>
                                <option value="45">Manisa</option>
                                <option value="46">Kahramanmaraş</option>
                                <option value="47">Mardin</option>
                                <option value="48">Muğla</option>
                                <option value="49">Muş</option>
                                <option value="50">Nevşehir</option>
                                <option value="51">Niğde</option>
                                <option value="52">Ordu</option>
                                <option value="53">Rize</option>
                                <option value="54">Sakarya</option>
                                <option value="55">Samsun</option>
                                <option value="56">Siirt</option>
                                <option value="57">Sinop</option>
                                <option value="58">Sivas</option>
                                <option value="59">Tekirdağ</option>
                                <option value="60">Tokat</option>
                                <option value="61">Trabzon</option>
                                <option value="62">Tunceli</option>
                                <option value="63">Şanlıurfa</option>
                                <option value="64">Uşak</option>
                                <option value="65">Van</option>
                                <option value="66">Yozgat</option>
                                <option value="67">Zonguldak</option>
                                <option value="68">Aksaray</option>
                                <option value="69">Bayburt</option>
                                <option value="70">Karaman</option>
                                <option value="71">Kırıkkale</option>
                                <option value="72">Batman</option>
                                <option value="73">Şırnak</option>
                                <option value="74">Bartın</option>
                                <option value="75">Ardahan</option>
                                <option value="76">Iğdır</option>
                                <option value="77">Yalova</option>
                                <option value="78">Karabük</option>
                                <option value="79">Kilis</option>
                                <option value="80">Osmaniye</option>
                                <option value="81">Düzce</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>



            <div class="row">
                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>District<span>*</span></p>
                        <div class="custom-dropdown">
                            <select id="edit_district" name="edit_district">
                                <option value="" disabled selected>Select your district</option>
                                <option value="district1">District 1</option>
                                <option value="district2">District 2</option>
                                <option value="district3">District 3</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>Neighborhood<span>*</span></p>
                        <select id="edit_neighborhood" name="edit_neighborhood">
                            <option value="" disabled selected>Select your neighborhood</option>
                            <option value="neighborhood1">Neighborhood 1</option>
                            <option value="neighborhood2">Neighborhood 2</option>
                            <option value="neighborhood3">Neighborhood 3</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="checkout__input">
                <p>Delivery Adress<span>*</span></p>
                <p class="small-text">
                    Kargonuzun size sorunsuz bir şekilde ulaşabilmesi için mahalle, cadde, sokak, bina gibi detay
                    bilgileri
                    eksiksiz girdiğinizden emin olun.
                </p>
                <textarea name="edit_address_note" id="edit_address_note"></textarea>
            </div>


            <div id="fieldsContainerEdit">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="checkout__input">
                            <p>Tax or National ID<span>*</span></p>
                            <input type="text" id="edit_tax_or_national" name="edit_tax_or_national"
                                placeholder="Tax/National ID">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="checkout__input">
                            <p>Tax Office<span>*</span></p>
                            <input type="text" id="edit_tax_office" name="edit_tax_office" placeholder="Tax Office">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="checkout__input">
                            <p>Company Name<span>*</span></p>
                            <input type="text" id="edit_company_name" name="edit_company_name"
                                placeholder="Company Name">
                        </div>
                    </div>
                    <div class="centercheckbox">
                        <input type="checkbox" id="edit_einvoice" name="edit_is_einvoice">
                        <label for="edit_einvoice">I use E-Invoice</label>
                    </div>
                </div>
            </div>

            <div class="btntext">
                <input type="submit" class="save-btn" value="Save the adress">
            </div>
        </form>
    </div>

    <div class="addressadd-modal-body" id="addressAddOverlay">
        <form method="POST" id="addressForm">
            <div class="modal-header">
                <h2>Add Address</h2>
                <button type="button" class="close-btn-address" onclick="
    closeModalAddressAdd()">X</button>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>Fist Name<span>*</span></p>
                        <input type="text" name="first_name" placeholder="First Name" required>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>Last Name<span>*</span></p>
                        <input type="text" name="last_name" placeholder="Last Name" required>
                    </div>
                </div>
            </div>
            <div class="checkout__input__checkbox">
                <input type="checkbox" id="myCheckbox" name="is_company">
                <label for="myCheckbox">Kurumsal</label>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>Phone<span>*</span></p>
                        <input id="phone" type="text" placeholder="0 (___) ___ __ __" maxlength="18"
                            name="phone_number">
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>Province<span>*</span></p>
                        <div class="custom-dropdown">
                            <select id="province" name="province_id">
                                <option value="0">Please Select Province</option>
                                <option value="1">Adana</option>
                                <option value="2">Adıyaman</option>
                                <option value="3">Afyonkarahisar</option>
                                <option value="4">Ağrı</option>
                                <option value="5">Amasya</option>
                                <option value="6">Ankara</option>
                                <option value="7">Antalya</option>
                                <option value="8">Artvin</option>
                                <option value="9">Aydın</option>
                                <option value="10">Balıkesir</option>
                                <option value="11">Bilecik</option>
                                <option value="12">Bingöl</option>
                                <option value="13">Bitlis</option>
                                <option value="14">Bolu</option>
                                <option value="15">Burdur</option>
                                <option value="16">Bursa</option>
                                <option value="17">Çanakkale</option>
                                <option value="18">Çankırı</option>
                                <option value="19">Çorum</option>
                                <option value="20">Denizli</option>
                                <option value="21">Diyarbakır</option>
                                <option value="22">Edirne</option>
                                <option value="23">Elazığ</option>
                                <option value="24">Erzincan</option>
                                <option value="25">Erzurum</option>
                                <option value="26">Eskişehir</option>
                                <option value="27">Gaziantep</option>
                                <option value="28">Giresun</option>
                                <option value="29">Gümüşhane</option>
                                <option value="30">Hakkâri</option>
                                <option value="31">Hatay</option>
                                <option value="32">Isparta</option>
                                <option value="33">Mersin</option>
                                <option value="34">İstanbul</option>
                                <option value="35">İzmir</option>
                                <option value="36">Kars</option>
                                <option value="37">Kastamonu</option>
                                <option value="38">Kayseri</option>
                                <option value="39">Kırklareli</option>
                                <option value="40">Kırşehir</option>
                                <option value="41">Kocaeli</option>
                                <option value="42">Konya</option>
                                <option value="43">Kütahya</option>
                                <option value="44">Malatya</option>
                                <option value="45">Manisa</option>
                                <option value="46">Kahramanmaraş</option>
                                <option value="47">Mardin</option>
                                <option value="48">Muğla</option>
                                <option value="49">Muş</option>
                                <option value="50">Nevşehir</option>
                                <option value="51">Niğde</option>
                                <option value="52">Ordu</option>
                                <option value="53">Rize</option>
                                <option value="54">Sakarya</option>
                                <option value="55">Samsun</option>
                                <option value="56">Siirt</option>
                                <option value="57">Sinop</option>
                                <option value="58">Sivas</option>
                                <option value="59">Tekirdağ</option>
                                <option value="60">Tokat</option>
                                <option value="61">Trabzon</option>
                                <option value="62">Tunceli</option>
                                <option value="63">Şanlıurfa</option>
                                <option value="64">Uşak</option>
                                <option value="65">Van</option>
                                <option value="66">Yozgat</option>
                                <option value="67">Zonguldak</option>
                                <option value="68">Aksaray</option>
                                <option value="69">Bayburt</option>
                                <option value="70">Karaman</option>
                                <option value="71">Kırıkkale</option>
                                <option value="72">Batman</option>
                                <option value="73">Şırnak</option>
                                <option value="74">Bartın</option>
                                <option value="75">Ardahan</option>
                                <option value="76">Iğdır</option>
                                <option value="77">Yalova</option>
                                <option value="78">Karabük</option>
                                <option value="79">Kilis</option>
                                <option value="80">Osmaniye</option>
                                <option value="81">Düzce</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>



            <div class="row">
                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>District<span>*</span></p>
                        <div class="custom-dropdown">
                            <select id="district" name="district_id" data-selected="">
                                <option value="" disabled selected>Select your district</option>
                                <option value="district1">District 1</option>
                                <option value="district2">District 2</option>
                                <option value="district3">District 3</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>Neighborhood<span>*</span></p>
                        <select id="neighborhood" name="neighborhood_id" data-selected="">
                            <option value="" disabled selected>Select your neighborhood</option>
                            <option value="neighborhood1">Neighborhood 1</option>
                            <option value="neighborhood2">Neighborhood 2</option>
                            <option value="neighborhood3">Neighborhood 3</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="checkout__input">
                <p>Delivery Adress<span>*</span></p>
                <p class="small-text">
                    Kargonuzun size sorunsuz bir şekilde ulaşabilmesi için mahalle, cadde, sokak, bina gibi detay
                    bilgileri
                    eksiksiz girdiğinizden emin olun.
                </p>
                <textarea name="address_note"></textarea>
            </div>


            <div id="fieldsContainer"></div> <!-- Placeholder for fields -->

            <div class="btntext">
                <input type="submit" class="save-btn" value="Save the adress">
            </div>
        </form>
    </div>




</div>




<script>
    document.getElementById('card-number').addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove all non-digits
        value = value.substring(0, 16); // Limit to 16 digits

        // Add spaces after every 4 digits
        let formatted = '';
        for (let i = 0; i < value.length; i += 4) {
            if (i > 0) formatted += ' ';
            formatted += value.substring(i, i + 4);
        }

        e.target.value = formatted;
    });
</script>


<?php
include 'footer.php';
?>