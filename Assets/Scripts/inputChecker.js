document.querySelectorAll('input[name="allergy_check"]').forEach((elem) => {
    elem.addEventListener('change', function () {
        document.getElementById('allergy').disabled = this.value === 'no';
        if (this.value === 'no') {
            document.getElementById('allergy').value = '';
        }
    });
});

document.querySelectorAll('input[name="disease_check"]').forEach((elem) => {
    elem.addEventListener('change', function () {
        document.getElementById('disease').disabled = this.value === 'no';
        if (this.value === 'no') {
            document.getElementById('disease').value = '';
        }
    });
});

// document.getElementById('birthday').addEventListener('change', function() {
//     const selectedDate = new Date(this.value);
//     const today = new Date();
//     today.setHours(0, 0, 0, 0);

//     if (selectedDate > today) {
//         document.getElementById('birthday-error').textContent = 'Date of birth cannot be in the future.';
//         document.getElementById('birthday-error').style.display = 'block';
//         document.getElementById('birthday-error').style.color = 'red';
//         this.value = ''; // Clear the invalid date
//     } else {
//         document.getElementById('birthday-error').textContent = '';
//         document.getElementById('birthday-error').style.display = 'none';
//     }
// });