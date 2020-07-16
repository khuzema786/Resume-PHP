<?php
require_once "pdo.php";
session_start();
$autos = [];
$stmt = $pdo->query("SELECT first_name, last_name, headline, profile_id FROM profile");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
  <title>Khuzema Zoher Khomosi</title>
  <?php require_once "bootstrap.php"; ?>
</head>

<body>
  <div class="container">
    <h1>Khuzema's Resume Registry</h1>
    <?php

    if (isset($_SESSION['login'])) {
      if (isset($_SESSION['failure'])) {
        echo '<p style="color:red;">' . $_SESSION['failure'] . '</p>';
        unset($_SESSION['failure']);
      }
      if (isset($_SESSION['success'])) {
        echo '<p style="color:green;">' . $_SESSION['success'] . '</p>';
        unset($_SESSION['success']);
      }
    ?>
      <a href="logout.php">Logout</a>
      <table border="1">
        <thead>
          <tr>
            <th>Name</th>
            <th>Headline</th>
            <th>Action</th>
          </tr>
        </thead>
        <?php
        foreach ($rows as $row) {
          echo ("<tr><td>");
          //echo ($row['first_name'] . " " . $row['last_name']);
          echo ("<a href='view.php?profile_id=" . $row['profile_id'] . "'>" . $row['first_name'] . " " . $row['last_name']  . "</a>");
          echo ("</td><td>");
          echo ($row['headline']);
          echo ("</td><td>");
          echo ('<a href="edit.php?profile_id=' . $row['profile_id'] . '">Edit</a>');
          echo (" / ");
          echo ('<a href="delete.php?profile_id=' . $row['profile_id'] . '">Delete</a>');
          echo ("</td></tr>");
          $profiles[] = $row;
        }
        if (empty($profiles)) {
          echo "No rows found";
        }
        ?>
      </table>

      <a href="add.php">Add New Entry</a>

    <?php
    }
    ?>
    <?php
    if (!isset($_SESSION['login'])) {
    ?>

      <a href="login.php">Please log in</a>

      <table border="1">
        <thead>
          <tr>
            <th>Name</th>
            <th>Headline</th>
          </tr>
        </thead>
        <?php
        foreach ($rows as $row) {
          echo ("<tr><td>");
          echo ("<a href='view.php?profile_id=" . $row['profile_id'] . "'>" . $row['first_name'] . " " . $row['last_name']  . "</a>");
          echo ("</td><td>");
          echo ($row['headline']);
          echo ("</td></tr>");
          $autos[] = $row;
        }
        ?>
      </table>
      Attempt to
      <a href="add.php">Add Data</a> without logging in
    <?php
    }
    ?>
  </div>
</body>

</html>