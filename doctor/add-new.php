<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <title>Doctor</title>
    <style>
        .popup {
            animation: transitionIn-Y-bottom 0.5s;
        }
    </style>
</head>
<body>
    <?php
    session_start();

    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Check if the user is logged in and is an admin
    if (isset($_SESSION["user"])) {
        if (empty($_SESSION["user"]) || $_SESSION['usertype'] != 'a') {
            header("location: ../login.php");
            exit();
        }
    } else {
        header("location: ../login.php");
        exit();
    }

    // Include database connection
    include("../connection.php");

    $error = '3'; // Default error (generic)

    // If the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Collect form data
        $name = $_POST['name'] ?? '';
        $nic = $_POST['nic'] ?? '';
        $spec = $_POST['spec'] ?? '';
        $email = $_POST['email'] ?? '';
        $tele = $_POST['Tele'] ?? '';
        $password = $_POST['password'] ?? '';
        $cpassword = $_POST['cpassword'] ?? '';

        // Validate form input
        if (!empty($name) && !empty($nic) && !empty($spec) && !empty($email) && !empty($tele) && !empty($password) && !empty($cpassword)) {
            // Check if passwords match
            if ($password === $cpassword) {
                // Check if email already exists in the webuser table
                $stmt = $database->prepare("SELECT * FROM webuser WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $error = '1'; // Email already exists
                } else {
                    // Hash the password before storing
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert into doctor table
                    $stmt1 = $database->prepare("INSERT INTO doctor (docemail, docname, docpassword, docnic, doctel, specialties) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt1->bind_param("ssssss", $email, $name, $hashed_password, $nic, $tele, $spec);

                    // Insert into webuser table
                    $stmt2 = $database->prepare("INSERT INTO webuser (email, usertype) VALUES (?, 'd')");
                    $stmt2->bind_param("s", $email);

                    // Execute both queries
                    if ($stmt1->execute() && $stmt2->execute()) {
                        $error = '4'; // Success
                    } else {
                        $error = '5'; // Query execution failed
                    }

                    // Close the statements
                    $stmt1->close();
                    $stmt2->close();
                }

                // Close the email-checking statement
                $stmt->close();
            } else {
                $error = '2'; // Passwords do not match
            }
        } else {
            $error = '6'; // Missing input data
        }
    }

    // Redirect back to the doctors page with the appropriate error message
    header("location: doctors.php?action=add&error=" . $error);
    exit();
    ?>
</body>
</html>
