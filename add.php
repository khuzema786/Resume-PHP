<?php
require_once "pdo.php";
session_start();
if (isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
}
function validatePos()
{
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['year' . $i])) continue;
        if (!isset($_POST['desc' . $i])) continue;

        $year = $_POST['year' . $i];
        $desc = $_POST['desc' . $i];

        if (strlen($year) == 0 || strlen($desc) == 0) {
            return "All fields are required";
        }

        if (!is_numeric($year)) {
            return "Position year must be numeric";
        }
    }
    return true;
}
function validateEdu()
{
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['edu_year' . $i])) continue;
        if (!isset($_POST['edu_school' . $i])) continue;

        $edu_year = htmlentities($_POST['edu_year' . $i]);
        $edu_school = htmlentities($_POST['edu_school' . $i]);

        if (strlen($edu_year) < 1 || strlen($edu_school) < 1) {
            return "All fields are required";
        }

        if (!is_numeric($edu_year)) {
            return "Year must be numeric";
        }
        return true;
    }
}
//$email=htmlentities($_SESSION['email']);
// Demand a GET parameter
if (!isset($_SESSION['login'])) {
    die('ACCESS DENIED');
}
if (
    isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['headline']) && isset($_POST['summary']) && isset($_POST['email'])
) {
    if (
        strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1
        || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1 || strlen($_POST['email']) < 1
    ) {
        $_SESSION['failure'] = 'All values are required';
        header('location: add.php');
        return;
    }
    if (strpos(htmlentities($_POST['email']), '@') ==  false) {
        $_SESSION['failure'] = "Bad email";
        header('location: add.php');
        return;
    }
    if (is_string(validatePos()) || is_string(validateEdu())) {
        if (is_string(validatePos())) {
            $_SESSION['failure'] = validatePos();
            header('location: add.php');
            return;
        }
        if (is_string(validateEdu())) {
            $_SESSION['failure'] = validateEdu();
            header('location: add.php');
            return;
        }
    } else {
        $stmt = $pdo->prepare('INSERT INTO Profile
            (user_id, first_name, last_name, email, headline, summary)
            VALUES ( :uid, :fn, :ln, :em, :he, :su)');
        $stmt->execute(
            array(
                ':uid' => $_SESSION['user_id'],
                ':fn' => $_POST['first_name'],
                ':ln' => $_POST['last_name'],
                ':em' => $_POST['email'],
                ':he' => $_POST['headline'],
                ':su' => $_POST['summary']
            )
        );
        $profile_id = $pdo->lastInsertId(); //Returns last added primary key
        //Position table

        $rank = 1;
        for ($i = 1; $i <= 9; $i++) {
            if (!isset($_POST['year' . $i])) continue;
            if (!isset($_POST['desc' . $i])) continue;

            $year = $_POST['year' . $i];
            $desc = $_POST['desc' . $i];
            $stmt = $pdo->prepare('INSERT INTO Position
        (profile_id, rank, year, description)
        VALUES ( :pid, :rank, :year, :desc)');

            $stmt->execute(
                array(
                    ':pid' => $profile_id,
                    ':rank' => $rank,
                    ':year' => $year,
                    ':desc' => $desc
                )
            );

            $rank++;
        }
        //Education

        $rank = 1;

        for ($i = 1; $i <= 9; $i++) {
            if (!isset($_POST['edu_year' . $i])) continue;
            if (!isset($_POST['edu_school' . $i])) continue;

            $edu_year = htmlentities($_POST['edu_year' . $i]);
            $edu_school = htmlentities($_POST['edu_school' . $i]);

            $stmt = $pdo->prepare("
                SELECT * FROM institution
                WHERE name = :edu_school LIMIT 1
            ");

            $stmt->execute([
                ':edu_school' => $edu_school,
            ]);

            $result = $stmt->fetch(PDO::FETCH_OBJ);

            if ($result) {
                $institution_id = $result->institution_id;
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO institution (name)
                    VALUES (:name)
                ");

                $stmt->execute([
                    ':name' => $edu_school,
                ]);

                $institution_id = $pdo->lastInsertId();
            }

            $stmt = $pdo->prepare("
                INSERT INTO education (profile_id, institution_id, rank, year)
                VALUES (:profile_id, :institution_id, :rank, :year)
            ");

            $stmt->execute([
                ':profile_id' => $profile_id,
                ':institution_id' => $institution_id,
                ':rank' => $rank,
                ':year' => $edu_year,
            ]);

            $rank++;
        }

        //redirect
        $_SESSION['success'] = "added";
        header('location: index.php');
        return;
    }
}

?>
<html>

<head>
    <title>Khuzema Zoher Khomosi</title>
    <?php require_once "bootstrap.php"; ?>
</head>

<Body>
    <h1>Adding Profile For <?= htmlentities($_SESSION['name']); ?></h1>
    <?php
    if (isset($_SESSION['failure'])) {
        echo '<p style="color:red;">' . $_SESSION['failure'] . '</p>';
        unset($_SESSION['failure']);
    }
    if (isset($_SESSION['success'])) {
        echo '<p style="color:green;">' . $_SESSION['success'] . '</p>';
        unset($_SESSION['success']);
    }

    ?>
    <form method="post">
        <p>First Name:
            <input type="text" name="first_name" size="60" /></p>
        <p>Last Name:
            <input type="text" name="last_name" size="60" /></p>
        <p>Email:
            <input type="text" name="email" size="30" /></p>
        <p>Headline:<br />
            <input type="text" name="headline" size="80" /></p>
        <p>Summary:<br />
            <textarea name="summary" rows="8" cols="80"></textarea>
            <p>
                Education: <input type="submit" id="addedu" value="+">

                <div id="edu_fields">
                    <!-- <div id="position1">
                    <p>Year: <input type="text" name="year1" value="">
                        <input type="button" value="-" onclick="$('#position1').remove();return false;"></p>
                    <textarea name="desc1" rows="8" cols="80"></textarea>
                </div> -->
                </div>
            </p>
            <p>
                Position: <input type="submit" id="addPos" value="+">
            </p>
            <div id="position_fields">
                <!-- <div id="position1">
                    <p>Year: <input type="text" name="year1" value="">
                        <input type="button" value="-" onclick="$('#position1').remove();return false;"></p>
                    <textarea name="desc1" rows="8" cols="80"></textarea>
                </div> -->
            </div>
            <p>
                <input type="submit" value="Add">
                <input type="submit" name="cancel" value="Cancel">
            </p>
    </form>
    </p>
</Body>

</html>
<script>
    countPos = 0;
    countedu = 0;
    // http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
    $(document).ready(function() {
        window.console && console.log('Document ready called');
        $('#addPos').click(function(event) {
            // http://api.jquery.com/event.preventdefault/
            event.preventDefault(); //old school way to return false...The preventDefault() method cancels the event if it is cancelable, meaning that the default action that belongs to the event will not occur.
            if (countPos >= 9) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            countPos++;
            window.console && console.log("Adding position " + countPos);
            $('#position_fields').append(
                '<div id="position' + countPos + '"> \
    <p>Year: <input type="text" name="year' + countPos + '" value="" /> \
        <input type="button" value="-" \ onclick="$(\'#position' + countPos + '\').remove();return false;"></p> \
    <textarea name="desc' + countPos + '" rows="8" cols="80"></textarea>\
</div>');
        });
        $('#addedu').click(function(event) {
            // http://api.jquery.com/event.preventdefault/
            event.preventDefault(); //old school way to return false...The preventDefault() method cancels the event if it is cancelable, meaning that the default action that belongs to the event will not occur.
            if (countedu >= 9) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            countedu++;
            window.console && console.log("Adding education " + countedu);
            $('#edu_fields').append(
                '<div id="edu' + countedu + '"> \
    <p>Year: <input type="text" name="edu_year' + countedu + '" value="" /> \
        <input type="button" value="-" \ onclick="$(\'#edu' + countedu + '\').remove();return false;"></p> \
        <p>School: <input class="school" type="text" name="edu_school' + countedu + '" value=""/> </p>  \
</div>');
            $('.school').autocomplete({
                source: "school.php"
            });
        });
    });
</script>