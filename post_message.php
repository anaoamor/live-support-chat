<?php
  // Include the main file
  include 'main.php';
  // Check if the user is logged-in
  if (!is_loggedin($pdo)) {
    // User isn't logged-in
    exit('error');
  }
  // Ensure the GET ID and msg params exists
  if (!isset($_POST['id'], $_POST['msg'])) {
    exit('error');
  }
  // Make sure the user is associated with the conversation
  $stmt = $pdo->prepare('SELECT id FROM conversations WHERE id = ? AND (account_sender_id = ? OR account_receiver_id = ?)');
  $stmt->execute([ $_POST['id'], $_SESSION['account_id'], $_SESSION['account_id'] ]);
  $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$conversation) {
    // The user isn't not associated with the conversation, output error
    exit('error');
  }

  // Insert the new message into the database
  $stmt = $pdo->prepare('INSERT INTO messages (conversation_id,account_id,msg,submit_date) VALUES (?,?,?,?)');
  $stmt->execute([ $_POST['id'], $_SESSION['account_id'], $_POST['msg'], date('Y-m-d H:i:s') ]);
  // Output success
  exit('success');
?>