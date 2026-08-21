<?php

session_start();

require("database.php");

$error = "";



if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim(
        $_POST['username']
    );

    $password = trim(
        $_POST['password']
    );


  
    $stmt = mysqli_prepare(
        $con,
        "SELECT
            user_id,
            username,
            password,
            role
         FROM users
         WHERE username = ?"
    );


    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $username
        );


        mysqli_stmt_execute($stmt);


        mysqli_stmt_bind_result(
            $stmt,
            $user_id,
            $db_username,
            $hashed_password,
            $role
        );


        $user_found =
            mysqli_stmt_fetch($stmt);


        mysqli_stmt_close($stmt);


       
        if (
            $user_found &&
            password_verify(
                $password,
                $hashed_password
            )
        ) {

            
            session_regenerate_id(true);


            $_SESSION['user_id'] =
                $user_id;

            $_SESSION['username'] =
                $db_username;

            $_SESSION['role'] =
                $role;

            $_SESSION['last_activity'] =
                time();


            
            if (
                isset(
                    $_POST['remember_me']
                )
            ) {

                $cookie_name = "user";

                $cookie_value =
                    $db_username;

                $expiration_time =
                    time()
                    + 60 * 60 * 24 * 30;


                setcookie(
                    $cookie_name,
                    $cookie_value,
                    $expiration_time,
                    "/"
                );
            }


            header(
                "Location: dashboard.php"
            );

            exit();


        } else {

            $error =
                "Username or password is incorrect.";
        }


    } else {

        error_log(
            "Login query error: "
            . mysqli_error($con)
        );

        $error =
            "Unable to process login. Please try again.";
    }
}

?>

<?php

$page_title = "User Login";
include("header.php");

?>

<div class="form-box">


    <h1>
        User Login
    </h1>


    <?php

    if (
        isset($_GET['timeout']) &&
        $_GET['timeout'] == "true"
    ) {

        echo "<p class='error'>
                Session expired due to inactivity.
                Please login again.
              </p>";
    }


    if (!empty($error)) {

        echo "<p class='error'>"
            . htmlspecialchars($error)
            . "</p>";
    }

    ?>


    <form
        action=""
        method="post"
        name="login"
    >


        <p>

            <label>
                Username:
            </label>

            <input
                type="text"
                name="username"
                placeholder="Username"
                required
            >

        </p>


        <p>

            <label>
                Password:
            </label>

            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >

        </p>


        <p class="checkbox-row">

            <input
                type="checkbox"
                name="remember_me"
                id="remember_me"
            >

            <label for="remember_me">
                Remember Me
            </label>

        </p>


        <p>

            <input
                name="submit"
                type="submit"
                value="Login"
            >

        </p>


    </form>


    <p>

        Not registered yet?

        <a href="registration.php">
            Register Here
        </a>

    </p>


</div>


<?php

include("footer.php");

?>
