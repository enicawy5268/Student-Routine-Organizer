<?php

session_start();

require("database.php");

$errors = [];

$username = "";
$email = "";



if (isset($_SESSION['user_id'])) {

    header("Location: dashboard.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $email = filter_var(
        $_POST['email'],
        FILTER_SANITIZE_EMAIL
    );
    $password = trim($_POST['password']);

    $role = "student";
    $reg_date = date("Y-m-d H:i:s");


   
    if (
        !preg_match(
            "/^[a-zA-Z0-9_]+$/",
            $username
        )
    ) {

        $errors['username'] =
            "Username can only contain letters, numbers and underscore.";
    }


   
    if (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $errors['email'] =
            "Please enter a valid email address.";
    }


   
    if (
        !preg_match(
            "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/",
            $password
        )
    ) {

        $errors['password'] =
            "Password must be at least 8 characters "
            . "with uppercase, lowercase, number "
            . "and special character.";
    }


   
    if (empty($errors)) {

        $stmt = mysqli_prepare(
            $con,
            "SELECT user_id
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

            mysqli_stmt_store_result($stmt);

            if (
                mysqli_stmt_num_rows($stmt) > 0
            ) {

                $errors['username'] =
                    "Username already exists.";
            }

            mysqli_stmt_close($stmt);

        } else {

            error_log(
                "Registration username check error: "
                . mysqli_error($con)
            );

            $errors['database'] =
                "Unable to process registration. Please try again.";
        }
    }


    
    if (empty($errors)) {

        $stmt = mysqli_prepare(
            $con,
            "SELECT user_id
             FROM users
             WHERE email = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $email
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_store_result($stmt);

            if (
                mysqli_stmt_num_rows($stmt) > 0
            ) {

                $errors['email'] =
                    "Email is already registered.";
            }

            mysqli_stmt_close($stmt);

        } else {

            error_log(
                "Registration email check error: "
                . mysqli_error($con)
            );

            $errors['database'] =
                "Unable to process registration. Please try again.";
        }
    }


  
    if (empty($errors)) {

        $hashed_password =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );


        $stmt = mysqli_prepare(
            $con,
            "INSERT INTO users
            (
                username,
                email,
                password,
                role,
                reg_date
            )
            VALUES (?, ?, ?, ?, ?)"
        );


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "sssss",
                $username,
                $email,
                $hashed_password,
                $role,
                $reg_date
            );


            if (
                mysqli_stmt_execute($stmt)
            ) {

                $success_message =
                    "You are registered successfully.";

            } else {

                error_log(
                    "Registration insert error: "
                    . mysqli_stmt_error($stmt)
                );

                $errors['database'] =
                    "Registration failed. Please try again.";
            }


            mysqli_stmt_close($stmt);

        } else {

            error_log(
                "Registration prepare error: "
                . mysqli_error($con)
            );

            $errors['database'] =
                "Registration failed. Please try again.";
        }
    }
}

?>

<?php

$page_title = "User Registration";
include("header.php");

?>

<div class="form-box">


    <h1>
        Student Registration
    </h1>


    <?php if (isset($success_message)) { ?>

        <p class="success">
            <?php
            echo htmlspecialchars(
                $success_message
            );
            ?>
        </p>

        <p>
            Click here to
            <a href="login.php">
                Login
            </a>
        </p>


    <?php } else { ?>


        <?php
        if (!empty($errors)) {

            foreach ($errors as $error) {

                echo "<p class='error'>"
                    . htmlspecialchars($error)
                    . "</p>";
            }
        }
        ?>


        <form
            name="registration"
            action=""
            method="post"
        >


            <p>

                <label>
                    Username:
                </label>

                <input
                    type="text"
                    name="username"
                    placeholder="Username"
                    value="<?php
                        echo htmlspecialchars(
                            $username
                        );
                    ?>"
                    required
                >

            </p>


            <p>

                <label>
                    Email:
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    value="<?php
                        echo htmlspecialchars(
                            $email
                        );
                    ?>"
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


            <p class="small-text">

                Password must contain at least
                8 characters, uppercase,
                lowercase, number and
                special character.

            </p>


            <p>

                <input
                    type="submit"
                    name="submit"
                    value="Register"
                >

            </p>


        </form>


        <p>

            Already registered?

            <a href="login.php">
                Login Here
            </a>

        </p>


    <?php } ?>


</div>


<?php

include("footer.php");

?>
