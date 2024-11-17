<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Page</title>

    <!-- Bootstrap for CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: lightgrey;
        }

        .container-custom {
            max-width: 600px;
            padding: 20px;
            margin-top: 50px;
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

        /*Navbar Brand Style*/
        .navbar-brand {
            color: lightgreen !important;
        }

        .navbar-brand {
            position: absolute;
            left: 50%;
            transform: translateX(-50%)
        }

        .nav-link {
            color: white !important;
        }

        .nav-link:hover {
            color: lightgreen !important;
        }
    </style>
</head>
<body>

    <!-- Dark Navbar with Hamburger Menu for Responsive Webpage -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid d-flex justify-content-between">
            <a class="navbar-brand mx-auto" href="#">ARAY</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="registration.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container container-custom text-center">
        <h1>Welcome to our site</h1>
        <br>
        <p>We here at ARAY aim to help those who wish to manage their nutrion. 
            Whether it is for medical reasons or you just wish to have a more healthy lifestyle 
            we here are here to help you manage that.</p>
        <p>Please login or register to continue!</p>
    </div>
    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
