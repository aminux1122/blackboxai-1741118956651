// Main JavaScript for Salon de Beaute

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize all popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Shopping Cart Functions
    const cartFunctions = {
        addToCart: function(productId, quantity = 1) {
            $.ajax({
                url: SITE_URL + '/ajax/cart.php',
                method: 'POST',
                data: {
                    action: 'add',
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Produit ajouté!',
                            text: 'Le produit a été ajouté à votre panier',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        updateCartCount(response.cartCount);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: response.message
                        });
                    }
                }
            });
        },

        updateQuantity: function(productId, quantity) {
            $.ajax({
                url: SITE_URL + '/ajax/cart.php',
                method: 'POST',
                data: {
                    action: 'update',
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        updateCartTotal(response.total);
                        updateCartCount(response.cartCount);
                    }
                }
            });
        },

        removeItem: function(productId) {
            Swal.fire({
                title: 'Êtes-vous sûr?',
                text: "Voulez-vous retirer ce produit du panier?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, retirer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: SITE_URL + '/ajax/cart.php',
                        method: 'POST',
                        data: {
                            action: 'remove',
                            product_id: productId
                        },
                        success: function(response) {
                            if (response.success) {
                                $(`#cart-item-${productId}`).fadeOut(300, function() {
                                    $(this).remove();
                                    updateCartTotal(response.total);
                                    updateCartCount(response.cartCount);
                                });
                            }
                        }
                    });
                }
            });
        }
    };

    // Appointment Booking Functions
    const appointmentFunctions = {
        loadAvailableSlots: function(employeeId, date) {
            $.ajax({
                url: SITE_URL + '/ajax/appointments.php',
                method: 'GET',
                data: {
                    action: 'get_slots',
                    employee_id: employeeId,
                    date: date
                },
                success: function(response) {
                    if (response.success) {
                        updateTimeSlots(response.slots);
                    }
                }
            });
        },

        bookAppointment: function(formData) {
            $.ajax({
                url: SITE_URL + '/ajax/appointments.php',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Rendez-vous confirmé!',
                            text: 'Votre rendez-vous a été enregistré avec succès'
                        }).then(() => {
                            window.location.href = SITE_URL + '/profile.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: response.message
                        });
                    }
                }
            });
        }
    };

    // Form Validation
    const validateForm = function(formId) {
        const form = document.getElementById(formId);
        if (!form) return true;

        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');

        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });

        return isValid;
    };

    // Image Preview
    const handleImagePreview = function(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    // Helper Functions
    function updateCartCount(count) {
        const cartBadge = document.querySelector('.cart-badge');
        if (cartBadge) {
            cartBadge.textContent = count;
            cartBadge.style.display = count > 0 ? 'inline' : 'none';
        }
    }

    function updateCartTotal(total) {
        const totalElement = document.getElementById('cart-total');
        if (totalElement) {
            totalElement.textContent = formatPrice(total);
        }
    }

    function updateTimeSlots(slots) {
        const slotsContainer = document.getElementById('time-slots');
        if (!slotsContainer) return;

        slotsContainer.innerHTML = '';
        slots.forEach(slot => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-outline-primary m-1';
            button.textContent = slot;
            button.onclick = () => selectTimeSlot(slot);
            slotsContainer.appendChild(button);
        });
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('fr-MA', {
            style: 'currency',
            currency: 'MAD'
        }).format(price);
    }

    // Event Listeners
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const quantity = document.querySelector(`#quantity-${productId}`)?.value || 1;
            cartFunctions.addToCart(productId, quantity);
        });
    });

    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.productId;
            cartFunctions.updateQuantity(productId, this.value);
        });
    });

    document.querySelectorAll('.remove-from-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            cartFunctions.removeItem(productId);
        });
    });

    // Initialize date picker if exists
    const datePicker = document.getElementById('appointment-date');
    if (datePicker) {
        datePicker.addEventListener('change', function() {
            const employeeId = document.getElementById('employee-id').value;
            appointmentFunctions.loadAvailableSlots(employeeId, this.value);
        });
    }

    // Form submissions
    const appointmentForm = document.getElementById('appointment-form');
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (validateForm('appointment-form')) {
                appointmentFunctions.bookAppointment(new FormData(this));
            }
        });
    }

    // Image upload preview
    document.querySelectorAll('.image-upload').forEach(input => {
        input.addEventListener('change', function() {
            handleImagePreview(this, this.dataset.previewId);
        });
    });
});
