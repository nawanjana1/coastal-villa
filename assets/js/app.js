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
            estimate.textContent = 'Dates select kalama estimated cost eka pennai.';
            return;
        }

        const start = new Date(`${bookingCheckIn.value}T00:00:00`);
        const end = new Date(`${bookingCheckOut.value}T00:00:00`);
        const nights = (end - start) / (1000 * 60 * 60 * 24);

        if (nights < 1) {
            estimate.textContent = 'Check-out date eka check-in date ekata passe thora ganna.';
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
            alert('Name eka enter karanna.');
            return;
        }

        if (!phonePattern.test(phone)) {
            event.preventDefault();
            alert('Valid contact number ekak enter karanna.');
            return;
        }

        if (password.length < 8) {
            event.preventDefault();
            alert('Password eka at least characters 8k wenna ona.');
        }
    });
}