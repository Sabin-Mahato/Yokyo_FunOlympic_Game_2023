<?php

session_start();

require "../config/connection.php";
require 'vendor/autoload.php'; 
use SendGrid\Mail\Mail;
use SendGrid\Mail\From;
use SendGrid\Mail\To;
use SendGrid\Mail\Subject;
use SendGrid\Mail\PlainTextContent;

$email = "";
$name = "";
$errors = array();

if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
}

if (!isset($_SESSION['last_attempt_time'])) {
    $_SESSION['last_attempt_time'] = 0;
}

$lockout_time = 20; // 20 seconds

// if user signup button
if (isset($_POST['signup'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $cpassword = mysqli_real_escape_string($conn, $_POST['cpassword']);

    // define password criteria
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $symbol = preg_match('@[^\w]@', $password);
    $length = strlen($password) >= 6;

    // check if passwords match
    if ($password !== $cpassword) {
        $errors['password'] = "Verify that the passwords do not match!";
    }

    // check if password meets criteria
    if (!$uppercase || !$lowercase || !$number || !$symbol || !$length) {
        $errors['password'] = "Password should have at least 6-8 characters, one uppercase letter, one lowercase letter, one number, and one symbol!";
    }

    $email_check = "SELECT * FROM usertable WHERE email = '$email'";
    $res = mysqli_query($conn, $email_check);
    if (mysqli_num_rows($res) > 0) {
        $errors['email'] = "The email you provided already exists in our system.";
    }

    if (count($errors) === 0) {
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $code = rand(999999, 111111);
        $status = "notverified";
        $insert_data = "INSERT INTO usertable (name, email, password, code, status)
                        values('$name', '$email', '$encpass', '$code', '$status')";
        $data_check = mysqli_query($conn, $insert_data);
        if ($data_check) {
            $sendgrid = new SendGrid('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');
    
            $mail = new Mail();
            $mail->setFrom(new From('sabinmahato31@gmail.com', 'sawbeen cdy'));
            $mail->addTo(new To($email, $name));
            $mail->setSubject(new Subject("Email Verification Code"));
            $mail->addContent(new PlainTextContent("Your verification code is $code"));
    
            try {
                $response = $sendgrid->send($mail);
                if ($response->statusCode() == 202) {
                    $info = "We've sent a verification code to your email - $email";
                    $_SESSION['info'] = $info;
                    $_SESSION['email'] = $email;
                    $_SESSION['password'] = $password;
                    header('location: signup-otp.php');
                    exit();
                } else {
                    $errors['otp-error'] = "Failed while sending code!";
                }
            } catch (Exception $e) {
                $errors['otp-error'] = "Failed while sending code! Error: " . $e->getMessage();
            }
        } else {
            $errors['db-error'] = "An error occurred while inserting data into the database!";
        }
    }
}

// if user clicks the verification code submit button
if (isset($_POST['check'])) {
    $_SESSION['info'] = "";
    $otp_code = mysqli_real_escape_string($conn, $_POST['otp']);
    $check_code = "SELECT * FROM usertable WHERE code = $otp_code";
    $code_res = mysqli_query($conn, $check_code);
    if (mysqli_num_rows($code_res) > 0) {
        $fetch_data = mysqli_fetch_assoc($code_res);
        $fetch_code = $fetch_data['code'];
        $email = $fetch_data['email'];
        $code = 0;
        $status = 'verified';
        $update_otp = "UPDATE usertable SET code = $code, status = '$status' WHERE code = $fetch_code";
        $update_res = mysqli_query($conn, $update_otp);
        if ($update_res) {
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            header('location: home.php');
            exit();
        } else {
            $errors['otp-error'] = "During code update, an error occurred!";
        }
    } else {
        $errors['otp-error'] = "The code you've provided is incorrect.";
    }
}

// if user clicks the login button
if (isset($_POST['login'])) {
    $current_time = time();
    if ($_SESSION['failed_attempts'] >= 3 && ($current_time - $_SESSION['last_attempt_time']) <= $lockout_time) {
        $errors['locked'] = "You've made an incorrect password entry thrice. Kindly hold on for 10 seconds before attempting another login.";
    } else {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $check_email = "SELECT * FROM usertable WHERE email = '$email'";
        $res = mysqli_query($conn, $check_email);
        if (mysqli_num_rows($res) > 0) {
            $fetch = mysqli_fetch_assoc($res);
            $fetch_pass = $fetch['password'];
            $role = $fetch['role']; // Assuming you have a 'role' column in the 'usertable' table

            if (password_verify($password, $fetch_pass)) {
                $_SESSION['failed_attempts'] = 0;
                $_SESSION['email'] = $email;
                $status = $fetch['status'];
                if ($status == 'verified') {
                    if ($role == 'Adminstrative') {
                        // Redirect to admin dashboard
                        header('location: main.php');
                    } elseif ($role == 'subscriber') {
                        // Redirect to subscriber dashboard
                        header('location: ../video-gallery.php');
                    }
                } else {
                    $info = "It appears that you have not yet verified your email address - $email";
                    $_SESSION['info'] = $info;
                    header('location: signup-otp.php');
                }
            } else {
                $_SESSION['failed_attempts'] += 1;
                $_SESSION['last_attempt_time'] = $current_time;
                $errors['email'] = "Incorrect email or password!";
            }
        } else {
            $errors['email'] = "We're sorry, you're not yet a member! Please click the link below to join.";
        }
    }
}

// if user clicks the continue button in the forgot password form
if (isset($_POST['check-email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $check_email = "SELECT * FROM usertable WHERE email='$email'";
    $run_sql = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($run_sql) > 0) {
        $code = rand(999999, 111111);
        $insert_code = "UPDATE usertable SET code = $code WHERE email = '$email'";
        $run_query =  mysqli_query($conn, $insert_code);

        if ($run_query) {
            $sendgrid = new SendGrid('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');

            $mail = new Mail();
            $mail->setFrom(new From('sabinmahato31@@gmail.com', 'sawbeen cdy'));
            $mail->addTo(new To($email, $name));
            $mail->setSubject(new Subject("Password Reset Code"));
            $mail->addContent(new PlainTextContent("Your password reset code is $code"));

            try {
                $response = $sendgrid->send($mail);
                if ($response->statusCode() == 202) {
                    $info = "Please check your email for a One-Time Password (OTP) to reset your password - $email";
                    $_SESSION['info'] = $info;
                    $_SESSION['email'] = $email;
                    header('location: reset-code.php');
                    exit();
                } else {
                    $errors['otp-error'] = "The code could not be sent!";
                }
            } catch (Exception $e) {
                $errors['otp-error'] = "An error occurred while sending the code! " . $e->getMessage();
            }
        } else {
            $errors['db-error'] = "Unfortunately, something went wrong!";
        }
    } else {
        $errors['email'] = "There is no such email address!";
    }
}

// if user clicks the check reset OTP button
if (isset($_POST['check-reset-otp'])) {
    $_SESSION['info'] = "";
    $otp_code = mysqli_real_escape_string($conn, $_POST['otp']);
    $check_code = "SELECT * FROM usertable WHERE code = $otp_code";
    $code_res = mysqli_query($conn, $check_code);
    if (mysqli_num_rows($code_res) > 0) {
        $fetch_data = mysqli_fetch_assoc($code_res);
        $email = $fetch_data['email'];
        $_SESSION['email'] = $email;
        $info = "It would be helpful if you created a new password that you don't use anywhere else.";
        $_SESSION['info'] = $info;
        header('location: new-password.php');
        exit();
    } else {
        $errors['otp-error'] = "It looks like you entered an incorrect code!";
    }
}

// if user clicks the change password button
if (isset($_POST['change-password'])) {
    $_SESSION['info'] = "";
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $cpassword = mysqli_real_escape_string($conn, $_POST['cpassword']);
    if ($password !== $cpassword) {
        $errors['password'] = "Verify that the passwords do not match!";
    } else {
        $code = 0;
        $email = $_SESSION['email']; // Assuming you've stored the email in the session
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $update_pass = "UPDATE usertable SET code = $code, password = '$encpass' WHERE email = '$email'";
        $run_query = mysqli_query($conn, $update_pass);
        if ($run_query) {
            $info = "You have successfully changed your password!";
            $_SESSION['info'] = $info;
            header('location: password-changed.php');
        } else {
            $errors['db-error'] = "There was a problem changing your password!";
        }
    }
}

?>
