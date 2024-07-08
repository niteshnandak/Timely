
// This for the register form validations for input
function keyup() {
    let first_name = document.getElementById("first_name");
    let last_name = document.getElementById("last_name");
    let email = document.getElementById("email");
    let phone_number = document.getElementById("phone_number");
    let submit = document.getElementById("submit");
    if (first_name.value.trim() && last_name.value.trim() && email.value.trim() && phone_number.value.trim()) {
        submit.disabled = false;
    } else {
        submit.disabled = true;
    }
}



// This for the register form validations for input[first_name,last_name]
function check_name(e) {
        if (!(e.key.length !== 0 && (e.key >= 'a' && e.key <= 'z') || (e.key >= 'A' && e.key <= 'Z'))) {
            e.preventDefault();
        }
}

// This for the register form validations for input[phone_number]
function check_phone_number(e){
        if (!(e.key.length !== 0 && e.key >= '0' && e.key <= '9')) {
            e.preventDefault();
        }
        phone_number_value = phone_number.value.trim()
        if (phone_number_value.length >= 10) {
            e.preventDefault();
        }
}

// This for the register form validations for submit
function check_submit(e){
    const regex = /^[a-z0-9]+@[a-z]+\.[a-z]{2,3}$/;
    let result = regex.test(email.value.trim());
    if (!result) {
        document.getElementById("here").innerHTML = "This is invalid Email Address Format";
        document.getElementById("here").hidden = false;
        submit.disabled = true;
    } else {
        document.getElementById("here").hidden = true;

    }
}


// This for the set_password  form validations
function check_password(){
    let password = document.getElementById("password").value;
    let password_confirmation = document.getElementById("password_confirmation").value;
    let password_error = document.getElementById("password_error");
    // console.log(password, password_confirmation);

    if ( (password.length < 7 )||(password != password_confirmation)) {
        password_error.hidden = false;
        if(password.length < 7){
            password_error.innerHTML = "Passwords should have atleast 8 characters";
            return;
        }
        if(password_confirmation.length <1){
            password_error.hidden = true;
        }else if(password != password_confirmation){
            console.log(password,password_confirmation)
            password_error.innerHTML = "Passwords Do Not Match";

        }


        document.getElementById("password_submit").disabled = true;
    } else {
        password_error.hidden = true;
        document.getElementById("password_submit").disabled = false;
    }

    // if(password_confirmation.length <1){
    //     console.log("hi");
    //
    // }
    console.log(password_confirmation.length)
}








// Disable submit button for create/Upload timesheet in Home page
document.addEventListener("DOMContentLoaded", function() {
    // Function to check if all required inputs in a form are filled
    function checkForm(form) {
        const requiredInputs = form.querySelectorAll("input[required]");
        let allFilled = true;

        requiredInputs.forEach(function(input) {
            if (!input.value.trim()) {
                allFilled = false;
            }
        });

        // Check if file input has a selected file (if exists)
        const fileInput = form.querySelector("input[type='file']");
        if (fileInput && fileInput.files.length === 0) {
            allFilled = false;
        }

        return allFilled;
    }

    // Get references to both forms and their submit buttons
    const timesheetForm = document.getElementById("timesheetForm");
    const timesheetSubmitButton = document.getElementById("submitButton");
    const uploadCsvForm = document.getElementById("uploadCsvForm");
    const uploadCsvSubmitButton = document.getElementById("uploadCsvSubmitButton");

    // Add event listeners to each form's input fields to check for changes
    timesheetForm.addEventListener("input", function() {
        timesheetSubmitButton.disabled = !checkForm(timesheetForm);
    });

    uploadCsvForm.addEventListener("input", function() {
        uploadCsvSubmitButton.disabled = !checkForm(uploadCsvForm);
    });

    // Initially disable submit buttons
    timesheetSubmitButton.disabled = true;
    uploadCsvSubmitButton.disabled = true;
});


// Disable download button from the Generate Reports Modal in the Invoices page unless atleast one field is filled
document.addEventListener("DOMContentLoaded", function() {

    // $('reportsModal').on('hidden.bs.modal', function() {
    //     return false;
    // });
    // $('reportsModal').on('hidden.bs.modal', function() {
    //     this.modal('show');
    // });

    // RESET Generate reports modal values
    var reportsModal = document.getElementById('reportsModal');
    // console.log(reportsModal);
    reportsModal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('reportForm').reset();
    });



    const reportForm = document.getElementById("reportForm");
    const downloadButton = document.getElementById("downloadBtn");

    // Function to check if any input field has a non-empty value
    function checkFields() {
        const inputFields = reportForm.querySelectorAll("input[type='text'], input[type='date']");
        for (let i = 0; i < inputFields.length; i++) {
            if (inputFields[i].value.trim() !== '') {
                return true; // At least one field is filled
            }
        }
        return false; // No field is filled
    }

    // Enable or disable the Download button based on field values
    function updateDownloadButton() {
        if (checkFields()) {
            downloadButton.removeAttribute("disabled");
        } else {
            downloadButton.setAttribute("disabled", "disabled");
        }
    }

    // Add event listeners to input fields to check for changes
    reportForm.addEventListener("input", function() {
        updateDownloadButton();
    });

    // Initially disable the Download button
    updateDownloadButton();
});
