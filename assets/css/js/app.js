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