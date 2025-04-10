<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the selected options from the form submission
    $selectedEmails = $_POST['sendTo'];

    // Check if the selectedEmails array is not empty
    if (!empty($selectedEmails)) {
        // Loop through the selected emails and do something with them
        foreach ($selectedEmails as $email) {
            // For demonstration purposes, we'll just echo the emails
            echo htmlspecialchars($email) . "<br>";
        }
    } else {
        echo "No emails selected.";
    }
}
?>
