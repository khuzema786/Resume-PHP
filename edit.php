<?php
require_once "pdo.php";
require_once "bootstrap.php";
session_start();

if (isset($_POST['cancel'])) {
    header("location:index.php");
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

if (
    isset($_POST['first_name']) && isset($_POST['summary']) && isset($_POST['last_name']) &&
    isset($_POST['email']) && isset($_POST['headline'])
) {
    // Data validation
    if (
        strlen($_POST['first_name']) < 1 || strlen($_POST['summary']) < 1 || strlen($_POST['last_name']) < 1
        || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1
    ) {
        $_SESSION['failure'] = 'All values are required';
        header('location: edit.php?profile_id=' . $_POST['profile_id']);
        return;
    }
    if (strpos($_POST['email'], '@') === false) {
        $_SESSION['failure'] = 'Bad Email';
        header('location: edit.php?profile_id=' . $_POST['profile_id']);
        return;
    }
    if (is_string(validatePos()) || is_string(validateEdu())) {
        if (is_string(validatePos())) {
            $_SESSION['failure'] = validatePos();
            header('location: edit.php?profile_id=' . $_POST['profile_id']);
            return;
        }
        if (is_string(validateEdu())) {
            $_SESSION['failure'] = validateEdu();
            header('location: edit.php?profile_id=' . $_POST['profile_id']);
            return;
        }
    } else {
        $stmt = $pdo->prepare('UPDATE profile SET first_name = :first_name, last_name = :last_name,
        email=:email,headline=:headline,summary=:summary WHERE profile_id = :profile_id');
        $stmt->execute(
            array(
                ':first_name' => $_POST['first_name'],
                ':last_name' => $_POST['last_name'],
                ':email' => $_POST['email'],
                ':headline' => $_POST['headline'],
                ':summary' => $_POST['summary'],
                ':profile_id' => $_POST['profile_id']
            )
        );
        // Clear out the old position entries
        $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
        $stmt->execute(array(':pid' => $_REQUEST['profile_id']));

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
                    ':pid' => $_REQUEST['profile_id'],
                    ':rank' => $rank,
                    ':year' => $year,
                    ':desc' => $desc
                )
            );

            $rank++;
        }
        // Clear out the old education entries
        $stmt = $pdo->prepare('DELETE FROM education WHERE profile_id=:pid');
        $stmt->execute(array(':pid' => $_REQUEST['profile_id']));
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
                ':profile_id' =>  $_REQUEST['profile_id'],
                ':institution_id' => $institution_id,
                ':rank' => $rank,
                ':year' => $edu_year
            ]);

            $rank++;
        }
    }

    $_SESSION['success'] = "Changed Succesfully";
    header('location: index.php');
    return;
}
// select from profile
$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :id AND user_id = :uid");
$stmt->execute(array(":id" => $_GET['profile_id'], ":uid" => $_SESSION['user_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row === false) {
    $_SESSION['failure'] = 'Bad value for profile_id';
    header('Location: index.php');
    return;
}
//select from position
$stmt = $pdo->prepare("SELECT year,description FROM position where profile_id = :id");
$stmt->execute(array(":id" => $_GET['profile_id']));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($rows === false) {
    $_SESSION['failure'] = 'Bad value for profile_id';
    header('Location: index.php');
    return;
}
//Education
$stmt = $pdo->prepare("SELECT * FROM education 
LEFT JOIN institution ON education.institution_id=institution.institution_id
WHERE profile_id = :pid");
$stmt->execute(array(":pid" => $_REQUEST['profile_id']));
$rowedu = $stmt->fetchAll(PDO::FETCH_ASSOC); //fetchall
if ($rowedu === false) {
    $_SESSION['failure'] = 'Bad value for profile_id';
    header('Location: index.php');
    return;
}

// Flash pattern
if (isset($_SESSION['failure'])) {
    echo '<p style="color:red;">' . $_SESSION['failure'] . "</p>\n";
    unset($_SESSION['failure']);
}
?>
<<h1> Editing Profile For <?= htmlentities($_SESSION['name']); ?></h1>

    <form method="post">
        <p>First Name:
            <input type="text" name="first_name" size="60" value="<?php echo $row['first_name'] ?>" /></p>
        <p>Last Name:
            <input type="text" name="last_name" size="60" value="<?php echo $row['last_name'] ?>" /></p>
        <p>Email:
            <input type="text" name="email" size="30" value="<?php echo $row['email'] ?>" /></p>
        <p>Headline:<br />
            <input type="text" name="headline" size="80" value="<?php echo $row['headline'] ?>" /></p>
        <p>Summary:<br />
            <textarea name="summary" rows="8" cols="80"><?php echo $row['summary'] ?></textarea>

            <p>
                Education: <input type="submit" id="addedu" value="+">
                <div id="edu_fields">
                    <?php
                    $rank = 1;
                    $countEduLen = 0;
                    foreach ($rowedu as $row) {
                        echo "<div id=\"edu" . $rank . "\">
                     <p>Year: <input type=\"text\" name=\"edu_year" . $rank . "\" value=\"" . $row['year'] . "\">
                     <input type=\"button\" value=\"-\" onclick=\"$('#edu" . $rank . "').remove();return false;\"></p>
                     <p>School:
                     <input class=\"school\" type=\"text\" name=\"edu_school" . $rank . "\" value=\"" . $row['name'] . " \"/></p> 
                     </div>";
                        $countEduLen = $rank;
                        $rank++;
                    } ?>
                </div>
            </p>
            <p>
                Position: <input type="submit" id="addPos" value="+"></p>
            <div id="position_fields">
                <?php
                $rank = 1;
                $countPosLen = 0;
                foreach ($rows as $row) {
                    echo "<div id=\"position" . $rank . "\">
                     <p>Year: <input type=\"text\" name=\"year" . $rank . "\" value=\"" . $row['year'] . "\">
                     <input type=\"button\" value=\"-\" onclick=\"$('#position" . $rank . "').remove();return false;\"></p>
                     <textarea name=\"desc" . $rank . "\" rows=\"8\" cols=\"80\">" . $row['description'] . "</textarea>
                     </div>";
                    $countPosLen = $rank;
                    $rank++;
                } ?>
            </div>
            <p>
                <input type="hidden" name=profile_id value=<?= $_GET['profile_id'] ?>> <input type="submit" value="Save">
                <input type="submit" name="cancel" value="Cancel">
            </p>
    </form>
    <script>
        var countedu = <?php echo $countEduLen; ?>;
        var countPos = <?php echo $countPosLen; ?>;
        window.console && console.log(countPos + countedu);
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
        <p>School: <input class="school" type="text" name="edu_school' + countedu + '" value=""/> </p> \
</div>');
                $('.school').autocomplete({
                    source: "school.php"
                });
            });
        });
    </script>