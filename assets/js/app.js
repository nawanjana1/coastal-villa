const checkIn = document.querySelector('#check_in');
const checkOut = document.querySelector('#check_out');

if (checkIn && checkOut) {
    const now = new Date();
    const localToday = new Date(
        now.getTime() - now.getTimezoneOffset() * 60000
    ).toISOString().slice(0, 10);

    checkIn.min = localToday;
    checkOut.min = localToday;

    checkIn.addEventListener('change', () => {
        checkOut.min = checkIn.value || localToday;

        if (checkOut.value && checkOut.value <= checkIn.value) {
            checkOut.value = '';
        }
    });
}

const bookingForm = document.querySelector('#booking-form');

if (bookingForm) {
    const bookingCheckIn = bookingForm.querySelector('#check_in');
    const bookingCheckOut = bookingForm.querySelector('#check_out');
    const estimate = document.querySelector('#booking-estimate');
    const pricePerNight = Number(bookingForm.dataset.price);

    function updateEstimate() {
        if (!bookingCheckIn.value || !bookingCheckOut.value) {
            estimate.textContent = 'Select your stay dates to see the estimated cost.';
            return;
        }

        const start = new Date(`${bookingCheckIn.value}T00:00:00`);
        const end = new Date(`${bookingCheckOut.value}T00:00:00`);
        const nights = (end - start) / (1000 * 60 * 60 * 24);

        if (nights < 1) {
            estimate.textContent = 'Choose a check-out date after the check-in date.';
            return;
        }

        const total = nights * pricePerNight;
        estimate.textContent =
            `${nights} night(s) × Rs. ${pricePerNight.toLocaleString()}`
            + ` = Rs. ${total.toLocaleString()}`;
    }

    bookingCheckIn.addEventListener('change', updateEstimate);
    bookingCheckOut.addEventListener('change', updateEstimate);
    updateEstimate();
}

const registrationForm = document.querySelector('#registration-form');

if (registrationForm) {
    registrationForm.addEventListener('submit', (event) => {
        const name = registrationForm.querySelector('#name').value.trim();
        const phone = registrationForm
            .querySelector('#contact_number').value.trim();
        const password = registrationForm
            .querySelector('#password').value;
        const phonePattern = /^\+?[0-9\s()-]{7,20}$/;

        if (!name) {
            event.preventDefault();
            alert('Please enter your name.');
            return;
        }

        if (!phonePattern.test(phone)) {
            event.preventDefault();
            alert('Please enter a valid contact number.');
            return;
        }

        if (password.length < 8) {
            event.preventDefault();
            alert('Password must be at least 8 characters long.');
        }
    });
}

const revealItems = document.querySelectorAll('.reveal');
if (revealItems.length) {
    document.body.classList.add('js-ready');

    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        revealItems.forEach((item) => revealObserver.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }
}

