<?php
require_once "pdo.php";
require_once "bootstrap.php";
session_start();
if (isset($_POST['cancel'])) {
    header("location:index.php");
    return;
}
if (isset($_POST['delete']) && isset($_POST['profile_id'])) {
    //delete from profile
    $stmt = $pdo->prepare('DELETE FROM profile WHERE profile_id=:id');
    $stmt->execute(
        array(
            ':id' => $_POST['profile_id']
        )
    );
    //delete from position
   /* $stmt = $pdo->prepare("DELETE FROM position where profile_id = :id");         No need to deletethem seperately once the foreign key table is deleted, all would be deleted on their own
    $stmt->execute(array(":id" => $_REQUEST['profile_id'])); 
    //delete from education
    $stmt = $pdo->prepare("DELETE FROM education where profile_id = :id");
    $stmt->execute(array(":id" => $_REQUEST['profile_id'])); */
    //Success
    $_SESSION['success'] = "Profile Deleted";
    header('location: index.php');
    return;
}
$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :id");
$stmt->execute(array(":id" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row === false) {
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
<h1>Confirm: Deleting Profile</h1>
<p>
    First Name: <?= htmlentities($row['first_name']); ?> <br>
    Last Name: <?= htmlentities($row['last_name']); ?> <br>
</p>

<form method="post">
    <input type="hidden" name="profile_id" value="<?= $_GET['profile_id'] ?>">
    <input type="submit" value="Delete" onclick="alert('Are you sure you want to delete the profile & all its content'); return true;" name="delete">
    <input type="submit" value="Cancel" name="cancel">
</form>