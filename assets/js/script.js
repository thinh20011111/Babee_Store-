/**
 * Babee Store - Main JavaScript file
 * Contains all interactions and functionality for the baby clothing e-commerce store
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize Bootstrap popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Product quantity selector
    const quantityInputs = document.querySelectorAll('.quantity-input');
    if (quantityInputs) {
        quantityInputs.forEach(input => {
            const decrementBtn = input.previousElementSibling;
            const incrementBtn = input.nextElementSibling;
            
            // Decrement button
            decrementBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const currentValue = parseInt(input.value);
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    // Trigger change event for forms that depend on this value
                    input.dispatchEvent(new Event('change'));
                }
            });
            
            // Increment button
            incrementBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const currentValue = parseInt(input.value);
                const maxValue = parseInt(input.getAttribute('max')) || 100;
                if (currentValue < maxValue) {
                    input.value = currentValue + 1;
                    // Trigger change event for forms that depend on this value
                    input.dispatchEvent(new Event('change'));
                }
            });
        });
    }
    
    // Quick add to cart buttons
    const quickAddBtns = document.querySelectorAll('.quick-add-btn');
    if (quickAddBtns) {
        quickAddBtns.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const productId = btn.getAttribute('data-product-id');
                
                // Change button to loading state
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
                btn.disabled = true;
                
                try {
                    // AJAX request to add item to cart
                    const response = await fetch('index.php?controller=cart&action=add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}&quantity=1`
                    });
                    
                    const data = await response.json();
                    
                    // Show success message
                    if (data.success) {
                        // Update cart count in the header
                        const cartCountBadge = document.querySelector('.cart-count');
                        if (cartCountBadge) {
                            cartCountBadge.textContent = data.cart_count;
                            cartCountBadge.classList.remove('d-none');
                        }
                        
                        // Show toast notification
                        const toast = new bootstrap.Toast(document.getElementById('cartToast'));
                        document.getElementById('cartToastBody').textContent = data.message;
                        toast.show();
                    } else {
                        alert(data.message);
                    }
                } catch (error) {
                    console.error('Error adding product to cart:', error);
                    alert('Failed to add product to cart. Please try again.');
                } finally {
                    // Restore button state
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            });
        });
    }
    
    // Cart quantity update
    const cartUpdateForms = document.querySelectorAll('.cart-update-form');
    if (cartUpdateForms) {
        cartUpdateForms.forEach(form => {
            const quantityInput = form.querySelector('.quantity-input');
            quantityInput.addEventListener('change', () => {
                form.submit();
            });
        });
    }
    
    // Product image gallery
    const mainImage = document.getElementById('main-product-image');
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    
    if (mainImage && thumbnails.length > 0) {
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', (e) => {
                e.preventDefault();
                const imageUrl = thumb.getAttribute('data-image');
                
                // Set active thumbnail
                thumbnails.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
                
                // Fade out main image, change source, fade in
                mainImage.style.opacity = '0';
                setTimeout(() => {
                    mainImage.setAttribute('src', imageUrl);
                    mainImage.style.opacity = '1';
                }, 300);
            });
        });
    }
    
    // Address form toggle in checkout
    const differentAddressCheckbox = document.getElementById('different-shipping-address');
    const shippingAddressForm = document.getElementById('shipping-address-form');
    
    if (differentAddressCheckbox && shippingAddressForm) {
        shippingAddressForm.style.display = differentAddressCheckbox.checked ? 'block' : 'none';
        
        differentAddressCheckbox.addEventListener('change', () => {
            shippingAddressForm.style.display = differentAddressCheckbox.checked ? 'block' : 'none';
        });
    }
    
    // Payment method toggle in checkout
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const paymentDetails = document.querySelectorAll('.payment-details');
    
    if (paymentMethods.length > 0 && paymentDetails.length > 0) {
        // Show details for the selected payment method
        const updatePaymentDetails = () => {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            paymentDetails.forEach(detail => {
                detail.style.display = 'none';
            });
            
            const selectedDetails = document.getElementById(`${selectedMethod}-details`);
            if (selectedDetails) {
                selectedDetails.style.display = 'block';
            }
        };
        
        // Initial update
        updatePaymentDetails();
        
        // Listen for changes
        paymentMethods.forEach(method => {
            method.addEventListener('change', updatePaymentDetails);
        });
    }
    
    // Order tracking form
    const trackingForm = document.getElementById('order-tracking-form');
    if (trackingForm) {
        trackingForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const orderNumber = document.getElementById('order_number').value;
            const email = document.getElementById('email').value;
            const resultContainer = document.getElementById('tracking-result');
            
            resultContainer.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Searching for your order...</p></div>';
            
            try {
                const response = await fetch(`index.php?controller=order&action=getTracking&order_number=${orderNumber}&email=${encodeURIComponent(email)}`);
                const data = await response.json();
                
                if (data.success) {
                    resultContainer.innerHTML = `
                        <div class="alert alert-success">
                            <h5>Order #${data.order.order_number}</h5>
                            <p class="mb-1"><strong>Status:</strong> ${data.order.status}</p>
                            <p class="mb-1"><strong>Date:</strong> ${data.order.created_at}</p>
                            <p class="mb-0"><strong>Shipping:</strong> ${data.order.shipping_method}</p>
                        </div>
                        <div class="tracking-timeline mt-4">
                            ${data.tracking.map(item => `
                                <div class="tracking-item">
                                    <div class="tracking-icon ${item.completed ? 'completed' : ''}">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="tracking-content">
                                        <h5>${item.status}</h5>
                                        <p class="text-muted small">${item.date}</p>
                                        <p>${item.description}</p>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                } else {
                    resultContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <p class="mb-0">${data.message}</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error tracking order:', error);
                resultContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <p class="mb-0">Failed to track your order. Please try again later.</p>
                    </div>
                `;
            }
        });
    }

    // Admin Dashboard charts (if on admin page)
    if (document.getElementById('salesChart')) {
        // Sales Chart
        var salesCtx = document.getElementById('salesChart').getContext('2d');
        var salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: salesChartData.labels,
                datasets: [{
                    label: 'Sales',
                    data: salesChartData.data,
                    backgroundColor: 'rgba(255, 107, 107, 0.2)',
                    borderColor: 'rgba(255, 107, 107, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Category Chart
        var categoryCtx = document.getElementById('categoryChart').getContext('2d');
        var categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryChartData.labels,
                datasets: [{
                    data: categoryChartData.data,
                    backgroundColor: [
                        'rgba(255, 107, 107, 0.7)',
                        'rgba(78, 205, 196, 0.7)',
                        'rgba(255, 180, 95, 0.7)',
                        'rgba(161, 134, 190, 0.7)',
                        'rgba(133, 193, 233, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    }
});