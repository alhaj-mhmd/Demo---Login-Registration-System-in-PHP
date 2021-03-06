<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: welcome.php");
    exit;
}

// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$secure =  $secure_err = "";


// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if secure is empty
    if (empty(trim($_POST["secure"]))) {
        $secure_err = "Please enter secure.";
    } else {
        $secure = trim($_POST["secure"]);
    }

   

    // Validate credentials
    if (empty($secure_err)) {
        // Prepare a select statement
        $sql = "SELECT id, secure , name FROM users WHERE secure = :secure";

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":secure", $param_secure, PDO::PARAM_STR);

            // Set parameters
            $param_secure = trim($_POST["secure"]);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Check if secure exists, if yes then verify password
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row["id"];
                        $secure_db = $row["secure"];
                    
                        $hashed_password = $row["password"];
                        if ($secure == $secure_db) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["name"] = $name;

                            // Redirect user to welcome page
                            header("location: reset-password.php");
                        } else {
                            // Display an error message if password is not valid
                            $secure_err = "The answer you entered was not valid.";
                        }
                    }
                } else {
                    // Display an error message if secure doesn't exist
                    $secure_err = "No account found with that secure.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            unset($stmt);
        }
    }

    // Close connection
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body {
            font: 14px sans-serif;
        }

        .wrapper {
            width: 350px;
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <h2>Forgot Password</h2>
        <p class="mb-4">Please answer the quesion.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($secure_err)) ? 'has-error' : ''; ?>">
                <label>what is your favorite book</label>
                <input type="text" name="secure" class="form-control">
                <span class="help-block"><?php echo $secure_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Forgot">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
        </form>
    </div>
</body>

</html>