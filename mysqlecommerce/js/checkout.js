function loadUserSelectedAddresses() {
  showLoader();
  fetch("tools/action/address_show_selected.php")
    .then(response => response.text()) // Get raw text first for debugging
    .then(rawText => {

      let data;
      try {
        data = JSON.parse(rawText); // Try to parse JSON manually
      } catch (e) {
        showToast("❌ Failed to parse JSON:" + e, true);
        return;
      }

      const deliveryContainer = document.getElementById("delivery-address-of-user");
      const billingContainer = document.getElementById("billing-address-of-user");

      if (!deliveryContainer || !billingContainer) {
        showToast("⚠️ One or both containers not found in the DOM.", true);
        return;
      }
      deliveryContainer.innerHTML = '';
      billingContainer.innerHTML = '';


      if (data.delivery_address.length > 0) {
        displayAddresses(data.delivery_address, deliveryContainer);
      } else {
        deliveryContainer.innerHTML = "<div class='addaddresscardcase' id='addressContainer'><button class='addaddresscard' onclick='openModalAddressAdd()'><span>+</span> Add new Address</button></div>";
      }
      if (data.billing_address.length > 0) {

        document.getElementById("billing-address").style.removeProperty("visibility");
        document.getElementById('sameforbothToggle').classList.remove('checked');
        document.querySelector(".tab-header").style.display = "flex";

        displayAddresses(data.billing_address, billingContainer);
      }
      else {
        document.getElementById("billing-address").style.visibility = "hidden";
        document.getElementById("billing-address-of-user").innerHTML = "";
        const toggle = document.getElementById('sameforbothToggle');
        const hiddenInput = document.getElementById('sameforbothHidden');

        toggle.classList.add('checked');
        if (hiddenInput) hiddenInput.value = '1';
      }
    })
  hideLoader();
}

function toggleFields() {
  const container = document.getElementById('fieldsContainerEdit');
  if (container.style.display === 'none' || container.style.display === '') {
    container.style.display = 'block';
  } else {
    container.style.display = 'none';
  }
}
function displayAddresses(addresses, myDiv) {

  let container;
  if (typeof myDiv === "string") {
    container = document.getElementById(myDiv);
  }
  else if (myDiv instanceof Element) {
    container = myDiv;
  }
  else if (myDiv && myDiv.nodeType === 1) {
    container = myDiv;
  }
  if (!container) {
    showToast("The provided div is not valid.", true);
    return;
  }
  if (!addresses || addresses.length === 0) {
    showToast("There are no value", true);
    return;
  }
  container.innerHTML = '';

  addresses.forEach(address => {
    let wrapperElement;

    const cardWrapper = document.createElement('div');
    cardWrapper.classList.add('card-wrapper');

    const contentWrapper = document.createElement('div');
    contentWrapper.classList.add('card');
    contentWrapper.classList.add('add-btn');
    //contentWrapper.setAttribute('onclick', 'selectCard(this)');
    contentWrapper.setAttribute("data-id", address.user_address_id);
    contentWrapper.setAttribute("id", "my-card-" + address.user_address_id); // Custom ID

    const neighborhood = document.createElement('div');
    neighborhood.classList.add('background-shape');

    const phoneNumber = document.createElement('div');
    phoneNumber.classList.add('phone-number');
    phoneNumber.textContent = address.user_address_phone;

    const textcontent = document.createElement('div');
    textcontent.classList.add('content');

    const nameText = document.createElement('h2');
    nameText.textContent = address.user_firstname + " " + address.user_lastname;

    const streetText = document.createElement('p');

    let restrictedNote = address.user_address_note.trim();
    restrictedNote = restrictedNote.replace(/[,\.]/g, '');
    if (restrictedNote.length > 35) {
      restrictedNote = restrictedNote.substring(0, 40) + "...";
    }
    streetText.textContent = restrictedNote;

    const cityText = document.createElement('p');
    cityText.textContent = address.province_name + " / " + address.district_name + " / " + address.neighborhood_name;

    textcontent.appendChild(nameText);
    textcontent.appendChild(streetText);
    textcontent.appendChild(cityText);

    const editText = document.createElement('div');
    editText.classList.add('edit-text');
    editText.innerHTML = '<span onclick="editAddress(' + address.user_address_id + ')">Edit</span>';

    contentWrapper.appendChild(editText);
    contentWrapper.appendChild(neighborhood);
    contentWrapper.appendChild(phoneNumber);
    contentWrapper.appendChild(textcontent);

    cardWrapper.appendChild(contentWrapper);

    // If it's a selectable div, add radio button outside the card
    if (myDiv === "delivery-modal-body" || myDiv === "billing-modal-body") {
      const label = document.createElement("label");
      label.classList.add("radio-wrapper");

      const radio = document.createElement("input");
      radio.type = "radio";
      radio.name = "selected_address";
      radio.value = address.user_address_id;

      label.appendChild(radio);
      label.appendChild(cardWrapper);
      wrapperElement = label;
    } else {
      wrapperElement = cardWrapper;
    }

    container.appendChild(wrapperElement);



  });
}
function loadDistricts(provinceId, districtSelectId, selectedDistrictId = null) {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'tools/action/address_fill_district.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.send('province_id=' + provinceId);

  xhr.onload = function () {
    if (xhr.status === 200) {
      try {
        var districts = JSON.parse(xhr.responseText);
        var districtSelect = document.getElementById(districtSelectId);
        districtSelect.innerHTML = '';

        var placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = 'Select a District';
        districtSelect.appendChild(placeholderOption);

        districts.forEach(function (district) {
          var option = document.createElement('option');
          option.value = district.district_id;
          option.textContent = district.district_name;

          if (selectedDistrictId && district.district_id == selectedDistrictId) {
            option.selected = true;
          }

          districtSelect.appendChild(option);
        });
      } catch (e) {
        showToast('Error parsing JSON:' + e, true);
      }
    }
  };
}
function loadNeighborhoods(districtId, neighborhoodSelectId, selectedNeighborhoodId = null) {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'tools/action/address_fill_neighborhood.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.send('district_id=' + districtId);

  xhr.onload = function () {
    if (xhr.status === 200) {
      try {
        var neighborhoods = JSON.parse(xhr.responseText);
        var neighborhoodSelect = document.getElementById(neighborhoodSelectId);
        neighborhoodSelect.innerHTML = '';

        var placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = 'Select a Neighborhood';
        neighborhoodSelect.appendChild(placeholderOption);

        neighborhoods.forEach(function (neighborhood) {
          var option = document.createElement('option');
          option.value = neighborhood.neighborhood_id;
          option.textContent = neighborhood.neighborhood_name;

          if (selectedNeighborhoodId && neighborhood.neighborhood_id == selectedNeighborhoodId) {
            option.selected = true;
          }

          neighborhoodSelect.appendChild(option);
        });
      } catch (e) {
        showToast('Error parsing JSON:' + e, true);
      }
    }
  };
}

document.getElementById('province').addEventListener('change', function () {
  loadDistricts(this.value, 'district');
});
function setupPhoneFormatter(inputId) {
  const phoneInput = document.getElementById(inputId);
  if (!phoneInput) return;

  phoneInput.addEventListener('input', function (e) {
    let digits = e.target.value.replace(/\D/g, '');

    // Force the first digit to be "0".
    if (!digits || digits[0] !== '0') {
      digits = '0' + digits;
    }

    // Limit the total digits to 11 (1 fixed zero + 10 digits).
    digits = digits.substring(0, 11);

    // Start building the formatted string.
    let formatted = '0';
    if (digits.length > 1) {
      // Get the digits after the first 0.
      let rest = digits.substring(1);
      // Part 1: next 3 digits for area code.
      let part1 = rest.substring(0, 3);
      formatted = '0 (' + part1;
      if (rest.length >= 3) {
        formatted += ') ';
      }
      // Part 2: next 3 digits.
      let part2 = rest.substring(3, 6);
      formatted += part2;
      if (rest.length >= 6) {
        formatted += ' ';
      }
      // Part 3: next 2 digits.
      let part3 = rest.substring(6, 8);
      formatted += part3;
      if (rest.length >= 8) {
        formatted += ' ';
      }
      // Part 4: next 2 digits.
      let part4 = rest.substring(8, 10);
      formatted += part4;
    }

    // Update the input with the formatted phone number.
    e.target.value = formatted;
  });
}

document.getElementById('orderForm').addEventListener('submit', function (e) {
  e.preventDefault(); // Stop default submit
  const cardNumber = document.getElementById('card-number').value.trim();
  const cardName = document.getElementById('card-name').value.trim();
  const cvv = document.getElementById('cvv').value.trim();
  const expiryMonth = document.getElementById('expiry_month').value.trim();
  const expiryYear = document.getElementById('expiry_year').value.trim();
  const responseMsg = document.getElementById('responseMsg');

  if (!cardNumber || !cardName || !cvv || !expiryMonth || !expiryYear) {
    showToast('Please fill in all card details.');
    responseMsg.focus();
    return;
  }

  const formData = new FormData(); // empty FormData object

  formData.append('card_number', cardNumber);
  formData.append('card_name', cardName);
  formData.append('cvv', cvv);
  formData.append('expiry_month', expiryMonth);
  formData.append('expiry_year', expiryYear);

  fetch('tools/action/user_place_order.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      const responseMsg = document.getElementById('responseMsg');

      if (data.status === 'success') {
        const orderId = data.order_id || 'unknown';
        // Redirect to the complete page with the order ID
        window.location.href = `${window.location.origin}${data.base_path}/tools/purchase-complete.php?order_id=${orderId}`;
      } else {
        // Show the error message clearly
        responseMsg.textContent = data.message || 'An error occurred.';
        responseMsg.style.color = 'red';
      }
    })

    .catch(err => {
      showToast('Error:' + err, true);
      document.getElementById('responseMsg').innerHTML = 'An unexpected error occurred.';
    });

});


document.addEventListener('DOMContentLoaded', function () {
  const checkoutOrder = document.querySelector('.checkout__order');
  const footer = document.querySelector('footer');
  const nav = document.querySelector('.topnav');

  // Height of the nav
  const navHeight = nav.offsetHeight;

  // Function to update position of the checkout order
  function updatePosition() {
    const windowWidth = window.innerWidth;  // Get the viewport width
    if (windowWidth < 992) {
      // If the viewport width is less than 992px, stop the sticky behavior
      checkoutOrder.style.position = 'static';
      return;
    }

    const scrollY = window.scrollY;
    const orderHeight = checkoutOrder.offsetHeight;

    const footerTop = footer.getBoundingClientRect().top + scrollY;  // Footer position
    const navTop = nav.getBoundingClientRect().top + scrollY;  // Nav position

    // If the scroll position is less than the top of the nav, keep the checkout order fixed at the top
    if (scrollY < navTop - orderHeight) {
      checkoutOrder.style.position = 'fixed';
      checkoutOrder.style.top = navHeight + 'px';  // Stay fixed just below the nav
    } else if (scrollY >= navTop - orderHeight && scrollY < footerTop - orderHeight - 100) {
      // When it's between the nav and the footer, position absolute and allow scrolling
      checkoutOrder.style.position = 'absolute';
      checkoutOrder.style.top = scrollY + 'px';  // Move with the scroll
    } else {
      // Once it reaches the footer, stop it above the footer
      checkoutOrder.style.position = 'absolute';
      checkoutOrder.style.top = footerTop - orderHeight - 100 + 'px';  // Adding some space above the footer
    }
  }

  // Update the position on scroll and resize
  window.addEventListener('scroll', updatePosition);
  window.addEventListener('resize', updatePosition);




  // Apply the formatter to both fields
  setupPhoneFormatter('phone');
  setupPhoneFormatter('edit_phone_number');

  // --- ADD MODE ---
  var province = document.getElementById('province');
  var district = document.getElementById('district');

  if (province && district) {
    var selectedDistrict = district.getAttribute('data-selected');
    loadDistricts(province.value, 'district', selectedDistrict);

    province.addEventListener('change', function () {
      loadDistricts(this.value, 'district');
    });

    district.addEventListener('change', function () {
      loadNeighborhoods(this.value, 'neighborhood');
    });
  }

  var neighborhood = document.getElementById('neighborhood');
  if (district && neighborhood) {
    var selectedNeighborhood = neighborhood.getAttribute('data-selected');
    if (district.value) {
      loadNeighborhoods(district.value, 'neighborhood', selectedNeighborhood);
    }
  }

  // --- EDIT MODE ---
  var editProvince = document.getElementById('edit_province');
  var editDistrict = document.getElementById('edit_district');

  if (editProvince && editDistrict) {
    var selectedEditDistrict = editDistrict.getAttribute('data-selected');
    loadDistricts(editProvince.value, 'edit_district', selectedEditDistrict);

    editProvince.addEventListener('change', function () {
      loadDistricts(this.value, 'edit_district');
    });

    editDistrict.addEventListener('change', function () {
      loadNeighborhoods(this.value, 'edit_neighborhood');
    });
  }

  var editNeighborhood = document.getElementById('edit_neighborhood');
  if (editDistrict && editNeighborhood) {
    var selectedEditNeighborhood = editNeighborhood.getAttribute('data-selected');
    if (editDistrict.value) {
      loadNeighborhoods(editDistrict.value, 'edit_neighborhood', selectedEditNeighborhood);
    }
  }



  document.getElementById("myCheckbox").addEventListener("change", function () {
    let container = document.getElementById("fieldsContainer");
    container.innerHTML = ""; // Clear existing fields

    if (this.checked) {
      let fieldsHTML = `
            <div class="row">
                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>Tax or National ID<span>*</span></p>
                        <input type="text" name="tax_or_national_id" placeholder="Tax/National ID" required>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>Tax Office<span>*</span></p>
                        <input type="text" name="tax_office" placeholder="Tax Office" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="checkout__input">
                        <p>Company Name<span>*</span></p>
                        <input type="text" name="company_name" placeholder="Company Name" required>
                    </div>
                </div>
                <div class="centercheckbox">
                  <input type="checkbox" id="eFaturaCheckbox" name="is_einvoice">
                  <label for="eFaturaCheckbox">I use E-Invoice</label>
                </div>
            </div>
        `;
      container.innerHTML = fieldsHTML;
    }
  });
  // Yeni adres ekleme formu
  document.getElementById('addressForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('tools/action/address_add.php', { method: 'POST', body: fd })
      .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status} ${r.statusText}`); return r.text(); })
      .then(t => { try { return JSON.parse(t); } catch { throw new Error('Geçersiz JSON'); } })
      .then(data => {
        if (data.status !== 'success') throw new Error(data.message || 'Adres eklenemedi');

        const newId = Number(data.address_id ?? data.id ?? 0);
        // KRİTİK DÜZELTME: string "0"/"1" için güvenli parse
        const isFirst = Number(data.is_first ?? data.isFirst ?? data.first) === 1;
        showToast('Adres eklendi.');
        if (typeof loadUserSelectedAddresses === 'function') loadUserSelectedAddresses();
        if (typeof closeModalAddressAdd === 'function') closeModalAddressAdd();
        if (!newId) return;
        if (!isFirst) {
          // Evet/Hayır barını ID ile aç
          openMakeDefaultConfirm(newId);
        }
      })
      .catch(err => showToast('İstek hatası: ' + err.message, true));
  });

  // Evet/Hayır alanı: EVET'te sadece deliveryId gönder
  function openMakeDefaultConfirm(addressId) {
    let bar = document.getElementById('defaultConfirmBar');
    if (!bar) {
      bar = document.createElement('div');
      bar.id = 'defaultConfirmBar';
      bar.innerHTML = `
      <div class="dcb-wrap">
        <span>Do you want to set this address as your default?</span>
        <div class="dcb-actions">
          <button type="button" class="dcb-yes">Yes</button>
          <button type="button" class="dcb-no">No</button>
        </div>
      </div>`;
      document.body.appendChild(bar);
    }

    bar.classList.remove('show');

    // wait ~600ms, then trigger animation
    setTimeout(() => {
      bar.classList.add('show');
    }, 600);

    bar.querySelector('.dcb-no').onclick = () => closeMakeDefaultConfirm();
    bar.querySelector('.dcb-yes').onclick = () => {
      closeMakeDefaultConfirm();
      fetch('tools/action/address-selected-toggle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ deliveryId: Number(addressId) })
      })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            showToast('Default address updated.');
            if (typeof loadUserSelectedAddresses === 'function') loadUserSelectedAddresses();
          } else {
            showToast(res.message || 'Adres güncellenemedi.', true);
          }
        })
        .catch(() => showToast('Sunucu hatası.', true));
    };
  }

  function closeMakeDefaultConfirm() {
    const bar = document.getElementById('defaultConfirmBar');
    if (bar) {
      bar.classList.remove('show'); // remove the visible state
    }
  }



  document.getElementById('editAddressForm').addEventListener('submit', function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    fetch('tools/action/user_address_edit_action.php', {
      method: 'POST',
      body: formData
    })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP Error! Status: ${response.status} - ${response.statusText}`);
        }
        // Get the raw response text first to see what it is
        return response.text(); // Return raw text for inspection
      })
      .then(text => {
        try {
          var data = JSON.parse(text);  // Parse it as JSON
          return data;
        } catch (e) {
          // If JSON parsing fails, log the error and the raw response
          throw new Error("Invalid JSON: " + e.message + " - Raw Response: " + text);
        }
      })
      .then(data => {
        if (data.status === "success") {
          loadUserSelectedAddresses();
          closeeditAddress();
        } else {
          showToast("Error: " + data.message, true);
        }
      })
      .catch(error => {
        showToast("Fetch error:" + error, true);
      });


  });

  function fetchUserAddresses(toModal) {
    const formData = new FormData();

    fetch('tools/action/user_addresses.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.text())
      .then(text => {
        try {
          return JSON.parse(text);
        } catch (error) {
          throw new Error("Invalid JSON response: " + error.message);
        }
      })
      .then(data => {
        hideLoader();

        if (data.status === 'success') {
          displayAddresses(data.addresses, toModal);

          // Now that addresses are displayed, fetch the selected one
          fetch("tools/action/address_show_selected.php")
            .then(response => response.text())
            .then(data => {
              let bringData;
              try {
                bringData = JSON.parse(data);
              } catch (e) {
                showToast("❌ Failed to parse JSON:" + e, true);
                return;
              }

              let endResult = null;
              if (toModal === "delivery-modal-body") {
                endResult = bringData.delivery_address[0]?.user_address_id;
              } else if (toModal === "billing-modal-body") {
                endResult = bringData.billing_address[0]?.user_address_id;
              }

              const card = document.querySelector(`#${toModal} .card[data-id="${endResult}"]`);
              if (card) {
                card.classList.add('selected');
                const radio = card.closest('label')?.querySelector('input[type="radio"]');
                if (radio) {
                  radio.checked = true;
                } else {
                  showToast("⚠️ Related radio button not found.", true);
                }
              } else {
                showToast("⚠️ Card not found in the modal body.", true);
              }
            });
        } else {
          showToast("Error: " + data.message, true);
        }
      })
      .catch(error => {
        showToast("Fetch error:" + error, true);
        const container = document.getElementById(toModal);
        if (container) {
          container.innerHTML = '<p class="error">Failed to load addresses.</p>';
        }
      });
  }

  document.getElementById("changeAddressBtn").addEventListener("click", changeDeliveryAddress);

  async function changeDeliveryAddress() {
    await fetchUserAddresses("delivery-modal-body");
    openModalAddress();
  }
  document.getElementById("changeBillingBtn").addEventListener("click", changeBillingAddress);
  async function changeBillingAddress() {
    await fetchUserAddresses("billing-modal-body");
    openModalBillingAddress();
  }

  document.getElementById("sameforbothToggle").addEventListener("click", function () {
    showLoader();
    const deliveryContainer = document.getElementById("delivery-address-of-user");
    const billingContainer = document.getElementById("billing-address-of-user");

    deliveryContainer.innerHTML = '';
    billingContainer.innerHTML = '';
    decidingaddress();

    showToast('Toogle Action for Billing address', false);
    hideLoader();
  });


  function decidingaddress() {
    fetch("tools/action/user_toggle_address.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      }
    })
      .then(async (response) => {
        const rawText = await response.text();

        if (!response.ok) {
          showToast("Raw server response:" + rawText, true);
          throw new Error(`Server returned ${response.status}`);
        }

        let data;
        try {
          data = JSON.parse(rawText);
        } catch (error) {
          showToast("Raw response:", rawText);
          throw error;
        }

        if (!data.success) {
          showToast("Server reported failure:", data.message || "Unknown error", true);
          throw new Error(data.message || "Unknown server error");
        }

        // Everything went well
        loadUserSelectedAddresses();
      })
      .catch((error) => {
        showToast("decidingaddress() failed:" + error, true);
      });
  }



});

function saveAddress(button) {
  const selectedRadio = document.querySelector('input[name="selected_address"]:checked');
  if (!selectedRadio) {
    showToast('Please select an address first!', true);
    return;
  }

  const card = selectedRadio.closest('.radio-wrapper').querySelector('.card');
  const addressId = card.getAttribute('data-id');

  const buttonId = button.id; // 'DeliverySaveBtn' or 'BillingSaveBtn'

  // Decide if it's delivery or billing
  let data = {};
  if (buttonId === 'DeliverySaveBtn') {
    data = { deliveryId: parseInt(addressId), billingId: null };
  } else if (buttonId === 'BillingSaveBtn') {
    data = { deliveryId: null, billingId: parseInt(addressId) };
  } else {
    showToast('Unknown button clicked!', true);
    return;
  }

  fetch('tools/action/address-selected-toggle.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        closeModalAddress();
        closeModalBillingAddress();
        loadUserSelectedAddresses();
      } else {
        showToast('Error: ' + result.message, true);
      }
    })
    .catch(error => {
      showToast('Fetch error:' + error, true);
    });
}


function showLoader() {
  const loader = document.getElementById("pageLoader");
  loader.classList.add("active");
}

function hideLoader() {
  const loader = document.getElementById("pageLoader");
  var duration = 1000; // Duration in milliseconds
  setTimeout(() => {
    loader.classList.remove("active");
  }, duration);
}


function openModalAddress() {

  document.getElementById('custom-modal-overlay').style.display = 'flex';
  document.getElementById('customModalOverlay').style.display = 'flex';
}

function closeModalAddress() {
  document.getElementById('custom-modal-overlay').style.display = 'none';
  document.getElementById('customModalOverlay').style.display = 'none';
}

function openModalBillingAddress() {
  document.getElementById('billingModalOverlay').style.display = 'flex';
  document.getElementById('custom-modal-overlay').style.display = 'flex';
}

function closeModalBillingAddress() {
  document.getElementById('billingModalOverlay').style.display = 'none';
  document.getElementById('custom-modal-overlay').style.display = 'none';
}
function openModalAddressAdd() {

  document.getElementById('custom-modal-overlay').style.display = 'flex';
  document.getElementById('addressAddOverlay').style.display = 'flex';
}
function closeModalAddressAdd() {

  document.getElementById('custom-modal-overlay').style.display = 'none';
  document.getElementById('addressAddOverlay').style.display = 'none';
}
function editAddress(addressId) {

  fetch('tools/action/user_address_show.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ address_id: addressId })
  })
    .then(res => {
      if (!res.ok) throw new Error('Network error: ' + res.status);
      return res.json();
    })
    .then(json => {
      if (!json.success) {
        showToast(json.message || 'Could not load address', true);
        return;
      }

      const d = json.data;

      // 2) populate form fields
      document.getElementById('edit_address_id').value = d.user_address_id;
      document.getElementById('edit_first_name').value = d.user_firstname;
      document.getElementById('edit_last_name').value = d.user_lastname;

      document.getElementById('edit_district').setAttribute('data-selected', d.district_id);
      document.getElementById('edit_neighborhood').setAttribute('data-selected', d.neighborhood_id);

      loadDistricts(d.province_id, 'edit_district', d.district_id);
      loadNeighborhoods(d.district_id, 'edit_neighborhood', d.neighborhood_id);

      document.getElementById('edit_province').value = d.province_id;
      document.getElementById('edit_phone_number').value = d.user_address_phone;
      document.getElementById('edit_address_note').value = d.user_address_note;

      if (d.user_address_type == 1) {
        document.getElementById('edit_is_company').checked = true;
        document.getElementById('fieldsContainerEdit').style.display = 'block';
        document.getElementById('edit_tax_or_national').value = d.user_address_taxornationalid;
        document.getElementById('edit_tax_office').value = d.user_address_tax_office;
        document.getElementById('edit_company_name').value = d.user_address_company_name;
        document.getElementById('edit_einvoice').checked = d.user_address_einvoice_taxpayer == 1;
      } else {
        document.getElementById('edit_is_company').checked = false;
        document.getElementById('fieldsContainerEdit').style.display = 'none';
      }

      document.getElementById('custom-modal-overlay').style.display = 'flex';
      document.getElementById('editAddressAddOverlay').style.display = 'flex';
    })
    .catch(err => {
      showToast('Fetch/JS error:' + err, true);
    });
}


function closeeditAddress() {

  document.getElementById('custom-modal-overlay').style.display = 'none';
  document.getElementById('editAddressAddOverlay').style.display = 'none';
}

var modal = document.getElementById("custom-modal-overlay");
window.onclick = function (event) {
  if (event.target == modal) {
    closeModalAddress();
    closeModalBillingAddress();
    closeModalAddressAdd();
    closeeditAddress();
  }
}