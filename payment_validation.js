function validatePaymentForm() {
    const amount = document.getElementById("amount").value.trim();
    const date = document.getElementById("payment_date").value.trim();
    const error = document.getElementById("paymentError");

    if (!amount || !date) {
        error.textContent = "All fields are required.";
        return false;
    }

    if (isNaN(amount) || parseFloat(amount) <= 0) {
        error.textContent = "Amount must be a valid positive number.";
        return false;
    }

    error.textContent = "";
    return true;
}
