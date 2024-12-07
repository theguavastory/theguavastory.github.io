<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('images/denr_bldg5.jpg');
            background-size: cover;
            background-position: right;
            background-repeat: no-repeat;
            margin: 0;
        }
        .login-container {
            width: 400px;
            padding: 40px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }
        .login-header {
            text-align: center;
        }
        .login-header img {
            width: 120px;
            height: auto;
            margin-bottom: 10px;
        }
        .login-header h3 {
            margin-bottom: 5px;
        }
        .login-header p {
            font-size: 14px;
            margin-bottom: 0;
        }
        .btn-login {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-login:hover {
            background-color: #0056b3;
        }
        .forgot-password {
            text-align: center;
            margin-top: 10px;
        }
        .register {
    display: flex;
    justify-content: center;
    margin-top: 15px; /* Adjust margin to control spacing */
}

.register-link {
    font-size: 14px; /* Reduced size */
    font-weight: bold;
    color: #fff;
    text-decoration: none;
    padding: 8px 16px; /* Slightly reduced padding */
    background-color: #006769;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out;
}

.register-link:hover {
    background-color:#0a8c8f;
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    text-decoration: none;
    color: #fff;
}
@media (max-width: 576px) {

    .login-container {
        width: 90%; /* Make the container responsive */
        padding: 20px; /* Reduce padding for smaller devices */
        margin-top: 20px; /* Add spacing from top */
        border-radius: 10px; /* Slightly more rounded for mobile aesthetics */
    }

    .login-header img {
        width: 80px; /* Smaller logo */
        margin-bottom: 10px;
    }

    .login-header h3 {
        font-size: 1.2em; /* Slightly smaller title */
        margin-bottom: 5px;
    }

    .login-header p {
        font-size: 12px; /* Adjust text size */
        margin-bottom: 10px; /* Add spacing for readability */
    }

    .btn-login {
        font-size: 14px; /* Smaller font for buttons */
        padding: 8px; /* Compact padding */
    }

    .forgot-password {
        font-size: 12px; /* Adjust text size */
        margin-top: 15px; /* Add spacing above */
    }

    .register {
        margin-top: 20px; /* Increase spacing above register button */
    }

    .register-link {
        font-size: 12px; /* Smaller font for the link */
        padding: 6px 12px; /* Compact padding */
        border-radius: 4px; /* Slightly less rounded */
    }

    .register-link:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Enhance hover effect */
    }
}

    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="images/logo.png" alt="logo.png">
            <h3>DENR-CENRO</h3>
            <p>Brgy. Duhat, Santa Cruz, Laguna</p>
            <br>
            <h3>Login</h3>
        </div>
        <?php
        if (isset($_GET['error'])) {
            echo '
            <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="errorModalLabel">Error</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            ' . htmlspecialchars($_GET['error']) . '
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            ';
        }
        ?>
        <form id="login-form" action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="userPassword" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-login">Login</button>
            <div class="register">
                <a href="#" class="register-link" data-toggle="modal" data-target="#registerModal">Create an Account</a>
            </div>
            <div class="forgot-password">
                <a href="forgot_password.php">Forgot Password?</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">Register</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form for registering a new user -->
                <form id="register-form" method="POST" action="register.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" maxlength="11" minlength="11" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            <?php
            if (isset($_GET['error'])) {
                echo '$("#errorModal").modal("show");';
            }
            ?>
        });
    </script>
</body>
</html>
