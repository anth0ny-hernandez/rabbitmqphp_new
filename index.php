<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome Page</title>
    <style>
        /* Page styling*/
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
            background: lightgrey;
        }

        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid black;
            border-radius: 8px;
            box-shadow: 0px 0px 50px lightgreen;
            background: white;
        }

        h1 {
            color: black;
        }

        p {
            color: darkslategrey;
        }
        .button-group {
            margin-top: 20px;
        }

        .button {
            display: inline-block;
            margin: 5px;
            padding: 10px 20px;
            color: white;
            background-color: blue;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
        }

        .button:hover {
            background-color: darkblue;
        }
        
        .logout-button {
            background-color: red;
        }

        .logout-button:hover {
            background-color: darkred;
        }

        .login-button {
            background-color: green;
        }

        .login-button:hover {
            background-color: darkgreen;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Welcome to our site</h1>
    <p>Please login or register</p>

    <div class="button-group">
        <a href="login.php" class="button login-button">Login</a>
        <a href="registration.php" class="button login-button">Register</a>
    </div>
</div>
</body>
</html>