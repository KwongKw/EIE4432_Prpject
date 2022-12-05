<?php
require('db.php');

// Second Check
if (!isset($_POST['uid'], $_POST['password'], $_POST['username'], $_POST['email'], $_POST['gender'], $_POST['birthday'])) {
    // Could not get the data that should have been sent.
    echo '<script>alert("Please complete the registration form")</script>';
    exit('Please complete the registration form!');
}
// Make sure the submitted registration values are not empty.
if (empty($_POST['uid'] || $_POST['password'] || $_POST['username'] || $_POST['email'] || $_POST['gender'] || $_POST['birthday'])) {
    // One or more values are empty.
    echo '<script>alert("Please complete the registration form")</script>';
    exit('Please complete the registration form');
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo '<script>alert("Email is not valid!")</script>';
    exit('Email is not valid!');
}

if (preg_match('/^[a-zA-Z0-9]+$/', $_POST['uid']) == 0) {
    echo '<script>alert("Username is not valid!")</script>';
    exit('Username is not valid!');
}

if (preg_match('/^[a-zA-Z0-9]+$/', $_POST['username']) == 0) {
    echo '<script>alert("Username is not valid!")</script>';
    exit('Username is not valid!');
}

if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
    echo '<script>alert("Password must be between 5 and 20 characters long!")</script>';
	exit('Password must be between 5 and 20 characters long!');
}

// We need to check if the account with that uid exists.
if ($stmt = $con->prepare('SELECT uid, password FROM UserRecords WHERE uid = ?')) {
    // Bind parameters (s = string, i = int, b = blob, etc), hash the password using the PHP password_hash function.
    $stmt->bind_param('s', $_POST['uid']);
    $stmt->execute();
    $stmt->store_result();
    // Store the result so we can check if the account exists in the database.
    if ($stmt->num_rows > 0) {
        // UID already exists
        echo '<script>alert("User ID exists, please choose another!")</script>';
    } else {
        // UID doesnt exists, insert new account
        if ($stmt = $con->prepare('INSERT INTO UserRecords (uid, password, username, email, gender, birthday) VALUES (?, ?, ?, ?, ?, ?)')) {
            // We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt->bind_param('ssssss', $_POST['uid'], $password, $_POST['username'], $_POST['email'], $_POST['gender'], $_POST['birthday']);
            $stmt->execute();
            echo '<script>alert("You have successfully registered, you can now login!")</script>';
        } else {
            // Something is wrong with the sql statement, check to make sure accounts table exists with all 3 fields.
            echo '<script>alert("Could not prepare statement!")</script>';
        }
    }
    $stmt->close();
} else {
    // Something is wrong with the sql statement, check to make sure accounts table exists with all 3 fields.
    echo '<script>alert("Could not prepare statement!")</script>';
}

$con->close();
?>