document.addEventListener("DOMContentLoaded", () => {
    const statusContainer = document.getElementById("status-container");

    // ✅ Apply coupon handler
    document.querySelectorAll(".apply-coupon-btn").forEach(button => {
        button.addEventListener("click", () => {
            const couponId = button.dataset.couponId;

            fetch("tools/action/coupon-apply.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "coupon_id=" + encodeURIComponent(couponId)
            })
                .then(res => res.text())
                .then(response => {
                    // ✅ Mark selected button
                    document.querySelectorAll(".apply-coupon-btn").forEach(b =>
                        b.classList.remove("selected-coupon")
                    );
                    button.classList.add("selected-coupon");

                    // ✅ Refresh total
                    fetch("/Mysqlecommerce/tools/action/shoppingcart-total.php")
                        .then(res => res.text())
                        .then(newTotal => {
                            document.querySelector(".cart-total").textContent = "₺" + newTotal;
                        });

                    // ✅ Refresh coupon line
                    fetch("/Mysqlecommerce/tools/action/shoppingcart-coupon.php")
                        .then(res => res.text())
                        .then(html => {
                            const box = document.getElementById("coupon-applied-box");
                            if (box) box.innerHTML = html;
                        });

                    showToast("🎉 Coupon applied successfully!");
                })
                .catch(err => {
                    console.error("Error applying coupon:", err);
                    showToast("❌ Failed to apply coupon.", true);
                });
        });
    });

    // ✅ Remove coupon handler
    document.addEventListener("click", function (e) {
        if (e.target && e.target.id === "remove-coupon-btn") {
            fetch("tools/action/coupon-remove.php", {
                method: "POST"
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Reload coupon box and total only (no full page reload)
                        fetch("/Mysqlecommerce/tools/action/shoppingcart-total.php")
                            .then(res => res.text())
                            .then(newTotal => {
                                document.querySelector(".cart-total").textContent = "₺" + newTotal;
                            });

                        fetch("/Mysqlecommerce/tools/action/shoppingcart-coupon.php")
                            .then(res => res.text())
                            .then(html => {
                                const box = document.getElementById("coupon-applied-box");
                                if (box) box.innerHTML = html;
                            });

                        showToast("✅ Coupon removed.");
                    } else {
                        showToast(data.message || "❌ Failed to remove coupon.", true);
                    }
                });
        }
    });

});


document.getElementById('apply-coupon-btn-by-click').addEventListener('click', function() {
    const couponCode = document.getElementById('coupon-code').value.trim();

    if (!couponCode) {
        document.getElementById('coupon-feedback').textContent = 'Please enter a coupon code.';
        return;
    }

    fetch('tools/action/coupon-apply.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'coupon_code=' + encodeURIComponent(couponCode)
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('coupon-feedback').textContent = data.message;

        if (data.success) {
            location.reload(); // Refresh page
        }
    })
    .catch(() => {
        document.getElementById('coupon-feedback').textContent = 'An error occurred.';
    });
});