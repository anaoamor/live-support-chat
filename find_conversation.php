<?php
  // Include the main file
  include 'main.php';
  // Check if the user is logged-in
  if (!is_loggedin($pdo)) {
    // User isn't logged-in
    exit('error');
  }
  // Update the account status to Waiting
  $stmt = $pdo->prepare('UPDATE accounts SET status = "Waiting" WHERE id = ?');
  $stmt->execute([ $_SESSION['account_id'] ]);
  // Check if the conversation was already created
  $stmt = $pdo->prepare('SELECT * FROM conversations WHERE (account_sender_id = ? OR account_receiver_id = ?) AND submit_date > date_sub(?, interval 1 minute)');
  $stmt->execute([ $_SESSION['account_id'], $_SESSION['account_id'], date('Y-m-d H:i:s') ]);
  $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
  // If the conversation exists, output the ID
  if ($conversation) {
    exit($conversation['id']);  
  }

  // If the user is an Operator, find guest accounts that have their status set to Waiting
  if ($_SESSION['account_role'] == 'Operator') {
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE role != "Operator" AND status = "Waiting" AND last_seen > date_sub(?, interval 1 minute)');
    // If the user is an Guest, find operator accounts that have their status set to Waiting
  } else {
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE role = "Operator" AND status = "Waiting" AND last_seen > date_sub(?, interval 1 minute)');
  }
  // Make sure to retrieve all accounts active in the last minute
  $stmt->execute([ date('Y-m-d H:i:s') ]);
  $account = $stmt->fetch(PDO::FETCH_ASSOC);
  
  // If account exists
  if ($account) {
    // Check if conversation exists between user1 and user2
    $stmt = $pdo->prepare('SELECT * FROM conversations WHERE (account_sender_id = ? OR account_receiver_id = ?) AND (account_sender_id = ? OR account_receiver_id = ?)');
    $stmt->execute([ $_SESSION['account_id'], $_SESSION['account_id'], $account['id'], $account['id'] ]);
    // Conversation doesn't exist, so create one
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
      // Insert the new conversation
      $stmt = $pdo->prepare('INSERT INTO conversations (account_sender_id,account_receiver_id,submit_date) VALUES (?,?,?)');
      $stmt->execute([ $_SESSION['account_id'], $account['id'], date('Y-m-d H:i:s')]);
      // Output conversation ID
      echo $pdo->lastInsertId();
      exit;       
    }  
  }
  exit('error');
?>