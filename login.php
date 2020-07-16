<?php // Do not put any HTML above this line
require_once "pdo.php";
session_start();
if (isset($_POST['cancel'])) {
    // Redirect the browser to game.php
    header("Location: index.php");
    return;
}

$salt = 'XyZzy12*_';
//$stored_hash = '1a52e17fa899cf40fb04cfc42e6352f1';  // Pw is php123

$failure = false;  // If we have no POST data
// Check to see if we have some POST data, if we do process it
if (isset($_POST['email']) && isset($_POST['pass'])) {

    $check = hash('md5', $salt . $_POST['pass']);
    $stmt = $pdo->prepare('SELECT user_id, name FROM users
    WHERE email = :em AND password = :pw');
    $stmt->execute(array(':em' => $_POST['email'], ':pw' => $check));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    /* if (strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1) {
        $_SESSION['failure'] = "Email and Password are required";
        header("location: login.php");
        return;
    } else ((filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) === false) {
        $_SESSION['failure'] = "Email must have an at-sign (@)";
        header("location: login.php");
        return;
    }*/
    /*elseif(strpos($_POST['email'],'@')===false) {
        $failure="Email must have an at-sign (@)";
    }*/



    if ($row == true) {
        $_SESSION['login'] = true;
        $_SESSION['name'] = $row['name'];
        $_SESSION['user_id'] = $row['user_id'];
        // Redirect the browser to index.php
        header("Location: index.php");
        return;
    } else {
        $_SESSION['failure'] = "Incorrect password";
        header("location: login.php");
        return;
        error_log("Login fail " . $_POST['email'] . " $check");
    }
}
/* else {
            $_SESSION['failure'] = "Incorrect password";
            header("location: login.php");
            return;
            error_log("Login fail " . $_POST['email'] . " $check");
        } */


// Fall through into the View
?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once "bootstrap.php"; ?>
    <title>Khuzema Zoher Khomosi</title>
</head>

<body>
    <div class="container">

        <h1>Please Log In</h1>
        <?php
        // Note triple not equals and think how badly double
        // not equals would work here...
        if (isset($_SESSION['failure'])) {
            echo '<p style="color:red;">' . $_SESSION['failure'] . '</p>';
            unset($_SESSION['failure']);
        }
        /*else(isset($_SESSION['success'])) {
            echo '<p style="red">'. $_SESSION['failure'] .'</p>' ;
            unset($_SESSION['failure']);
         }*/

        ?>

        <form method="POST" class="form-horizontal">
            <div class="form-group">
                <label class="control-label col-sm-2" for="nam">Email:</label>
                <div class="col-sm-3">
                    <input class="form-control" type="text" name="email" id="nam">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-2" for="id_1723">Password:</label>
                <div class="col-sm-3">
                    <input class="form-control" type="password" name="pass" id="id_1723">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-2 col-sm-offset-2">
                    <input class="btn btn-primary" type="submit" onclick="return doValidate();" value="Log In">
                    <input class="btn" type="submit" name="cancel" value="Cancel">
                </div>
            </div>
        </form>
        <script>
            function doValidate() {
                console.log('Validating...');
                try {
                    email = document.getElementById('nam').value;
                    pw = document.getElementById('id_1723').value;
                    console.log("Validating pw=" + pw);
                    console.log("Validating email=" + email);
                    if (pw == null || pw == "" || email == null || email == "") {
                        alert("Both fields must be filled out");
                        return false;
                    }
                    if(email.includes('@')==false)
                    {
                        alert("Email address invalid");
                        return false;
                    }
                    return true;
                } catch (e) {
                    return false;
                }
                return false;
            }
        </script>

    </div>
</body>

</html>