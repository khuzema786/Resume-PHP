<?php
require_once "pdo.php";
require_once "bootstrap.php";
session_start();
$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :id");
$stmt->execute(array(":id" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);  //fetch

if ($row === false) {
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
//Position
$stmt = $pdo->prepare("SELECT year,description FROM position where profile_id = :pid");
$stmt->execute(array(":pid" => $_REQUEST['profile_id']));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC); //fetchall

?>
<<h1> Profile Information </h1>

    <p>First Name:
        <?= htmlentities($row['first_name']) ?></p>
    <p>Last Name:
        <?= htmlentities($row['last_name']) ?></p>
    <p>Email:
        <?= htmlentities($row['email']) ?></p>
    <p>Headline:<br />
        <?= htmlentities($row['headline']) ?></p>
    <p>Summary:<br />
        <?= htmlentities($row['summary']) ?><p>
        </p>
        <p>Education: <br />
            <ul>
                <?php
                foreach ($rowedu as $row) {
                    echo ('<li>' . $row['year'] . ':' . $row['name'] . '</li>');
                } ?>
            </ul>
        </p>
        <p>Position: <br />
            <ul>
                <?php
                foreach ($rows as $row) {
                    echo ('<li>' . $row['year'] . ':' . $row['description'] . '</li>');
                } ?>
            </ul>
        </p>
        <a href="index.php">Done</a>