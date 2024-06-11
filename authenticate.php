<?php
// include the main file
include 'main.php';
// Validate the form data
if (!isset($_POST['name'], $_POST['email'])) {
  exit('Please enter a valid name and email address!');
}
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
  exit('Please enter a valid email address!');
}

// Select account from the database based on the email address
$stmt = $pdo->prepare('SELECT * FROM accounts WHERE email = ?');
$stmt->execute([ $_POST['email'] ]);
// Fetch the results and return them as an associative array
$account = $stmt->fetch(PDO::FETCH_ASSOC);

// Does the account exist?
if ($account) {
  // Yes, it does... Check whether the user is an operator or guest

  if (isset($_POST['password']) && $account['role'] == 'Operator') {
    // User is an operator and provided a password
    if (password_verify($_POST['password'], $account['password'])) {
      // Password is correct! Authenticate the operator
      $_SESSION['account_loggedin'] = TRUE;
      $_SESSION['account_id'] = $account['id'];
      $_SESSION['account_role'] = $account['role']; 
      // Update the secret code
      update_secret($pdo, $account['id'], $account['email'], $account['secret']);
      // Ouput: success
      exit('success');
    } else {
      // Invalid password
      exit('Invalid credentials!');
    }
  } else if ($account['role'] == 'Operator') {
      // User is operator, so show the password input field on the front-end
      exit('operator');
  } else if ($account['role'] == 'Guest') {
      // User is a guest
      // Authenticate the guest
      // Guest don't need a password
      $_SESSION['account_loggedin'] = TRUE;
      $_SESSION['account_id'] = $account['id'];
      $_SESSION['account_role'] = $account['role']; 
      // Update secret code
      update_secret($pdo, $account['id'], $account['email'], $account['secret']);
      // Output: success
      exit('success');
  }
} else {
  // Accounts doesn't exist, so create one

  $stmt = $pdo->prepare('INSERT INTO accounts (email, password, full_name, role, last_seen) VALUES (?, ?, ?, ?, ?)');
  $stmt->execute([ $_POST['email'], '', $_POST['name'] ? $_POST['name'] : 'Guest', 'Guest', date('Y-m-d H:i:s') ]);
  // Retrieve the account ID
  $id = $pdo->lastInsertId();
  // Authenticate the new user
  $_SESSION['account_loggedin'] = TRUE;
  $_SESSION['account_id'] = $id;   
  $_SESSION['account_role'] = 'Guest'; 
  // Update secret code
  update_secret($pdo, $id, $_POST['email']);
  // Output: success
  exit('success');
}