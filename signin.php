<?php
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Registration page</title>
        <style>
       *{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Arial',sans-serif;
       }
       body{
        background: #f0f2f5;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        
       }
       .registration-form{
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
       }
       .registration-form h1{
        text-align: center;
        margin-bottom: 20px;
        color: #a50c2a;
        font-size: 25px;
       }
       .form-group{
        margin-bottom: 15px;
       }
       .form-group label{
        display: block;
        margin-bottom: 5px;
        color: #555;
        font-weight: 500;        
       }
       .form-group input{
        width: 100%;
        padding: 12px;
        border: 1px solid black;
        border-radius: 5px;
        font-size: 16px;
        transition: border 0.3s;
       }
        .form-group input:focus{
            border-color:rgb(130, 174, 239);
            outline: none;
        }
        .submit-btn{
            width: 100%;
            padding: 12px;
            background: #a50c2a;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .submit-btn:hover{
            background:rgb(123, 115, 114);
        }
        .error{
            color: #d32f2f;
            font-size:12px;
            margin-top: 5px;
        }
        .success{
            color: #388e3c;
            font-size:14px;
            margin-bottom: 15px;
            text-align: center;
        }
        .login-link{
            text-align: center;
            margin-top: 15px;
        }
        .login-link a{
            color: #a50c2a;
            text-decoration: none;
        }
        .login-link a:hover{
            text-decoration: underline;
        }

        </style>
    </head>
    <body>
        <div class="registration-form">
            <h1>Create an Account</h1>
            
            <?php if (isset($_SESSION['errors'])): ?>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>
            
            <form method="POST" action="register_handler.php">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter password" required>
                </div>
                <button type="submit" class="submit-btn">Register</button>
            </form>
            
            <div class="login-link">
                <a href="login.php">Already have an account? Login here</a>
            </div>
        </div>
    </body>
</html>