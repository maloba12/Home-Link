// HomeLink - Main JavaScript File

// Favorite functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle favorite buttons
    const favoriteButtons = document.querySelectorAll('.btn-favorite');
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const propertyId = this.dataset.propertyId;
            toggleFavorite(propertyId, this);
        });
    });
    
    // Booking modal
    const bookButtons = document.querySelectorAll('.btn-book');
    const bookingModal = document.getElementById('bookingModal');
    const modalClose = document.querySelector('.modal-close');
    
    bookButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (bookingModal) {
                bookingModal.style.display = 'block';
            }
        });
    });
    
    if (modalClose) {
        modalClose.addEventListener('click', function() {
            if (bookingModal) {
                bookingModal.style.display = 'none';
            }
        });
    }
    
    window.addEventListener('click', function(event) {
        if (event.target === bookingModal) {
            bookingModal.style.display = 'none';
        }
    });
    
    // Booking form submission
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitBooking(this);
        });
    }
});

// Toggle favorite
function toggleFavorite(propertyId, button) {
    fetch('../api/toggle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            property_id: propertyId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('active');
            const icon = button.querySelector('i');
            if (button.classList.contains('active')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                button.innerHTML = '<i class="fas fa-heart"></i> Saved';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                button.innerHTML = '<i class="far fa-heart"></i> Save';
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Submit booking
function submitBooking(form) {
    const formData = new FormData(form);
    
    fetch('../api/submit_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Booking submitted successfully!');
            document.getElementById('bookingModal').style.display = 'none';
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Change main image on thumbnail click
function changeMainImage(thumbnail) {
    const mainImg = document.getElementById('main-img');
    if (mainImg && thumbnail.src) {
        mainImg.src = thumbnail.src;
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
        thumbnail.classList.add('active');
    }
}

// Search form enhancement
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-form input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Could implement autocomplete here
        });
    }
});

// Form validation
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('error');
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

