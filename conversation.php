<?php
  // Include the main file
  include 'main.php';
  // Check if the user is logged-in
  if (!is_loggedin($pdo)) {
    // User isn't logged-in
    exit('error');
  }
  // Ensure the GET ID param exists
  if (!isset($_GET['id'])) {
    exit('error');
  }
  // Update the account status to Occupied
  $stmt = $pdo->prepare('UPDATE accounts SET status = "Occupied" WHERE id = ?');
  $stmt->execute([ $_SESSION['account_id'] ]);
  // Retrieve the conversation based on the GET ID param and account ID
  $stmt = $pdo->prepare('SELECT c.*, m.msg, a.full_name AS account_sender_full_name, a2.full_name AS account_receiver_full_name FROM conversations c JOIN accounts a ON a.id = c.account_sender_id JOIN accounts a2 ON a2.id = c.account_receiver_id LEFT JOIN messages m ON m.conversation_id = c.id WHERE c.id = ? AND (c.account_sender_id = ? OR c.account_receiver_id = ?)');
  $stmt->execute([ $_GET['id'], $_SESSION['account_id'], $_SESSION['account_id'] ]);
  $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
  // If the conversation doesn't exist
  if (!$conversation) {
    exit('error');
  }

  // Retrieve all messages based on the conversation ID
  $stmt = $pdo->prepare('SELECT * FROM messages WHERE conversation_id = ? ORDER BY submit_date ASC');
  $stmt->execute([ $_GET['id'] ]);
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  // Group all messages by the submit date
  $messages = [];
  foreach ($results as $result) {
    $messages[date('y/m/d', strtotime($result['submit_date']))][] = $result;
  }
  // Conversation template below
?>
<div class="chat-widget-messages">
  <p class="date">You're now chatting with <?=htmlspecialchars($_SESSION['account_id']==$conversation['account_sender_id']?$conversation['account_receiver_full_name']:$conversation['account_sender_full_name'], ENT_QUOTES)?>!</p>
  <?php foreach ($messages as $date => $array): ?>
  <p class="date"><?=$date==date('y/m/d')?'Today':$date?></p>
  <?php foreach ($array as $message): ?>
  <div class="chat-widget-message<?=$_SESSION['account_id']==$message['account_id']?'':' alt'?>" title="<?=date('H:i\p\m', strtotime($message['submit_date']))?>"><?=htmlspecialchars($message['msg'], ENT_QUOTES)?></div>
  <?php endforeach; ?>
  <?php endforeach; ?>
</div>
<form action="post_message.php" method="post" class="chat-widget-input-message" autocomplete="off">
  <input type="text" name="msg" placeholder="Message">
  <input type="hidden" name="id" value="<?=$conversation['id']?>">
</form>
