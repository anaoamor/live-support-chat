<?php
  include 'main.php';
  // Check if the user is logged-in
  if (!is_loggedin($pdo)) {
      // User isn't logged-in
      exit('error');
  }
  // Update the account status to Idle
  $stmt = $pdo->prepare('UPDATE accounts SET status = "Idle" WHERE id = ?');
  $stmt->execute([ $_SESSION['account_id'] ]);

  // Retrieve all the conversations associated with the user along with the most recent message
  $stmt = $pdo->prepare('SELECT c.*, (SELECT msg FROM messages WHERE conversation_id = c.id ORDER BY submit_date DESC LIMIT 1) AS msg, (SELECT submit_date FROM messages WHERE conversation_id = c.id ORDER BY submit_date DESC LIMIT 1) AS msg_date, a.full_name AS account_sender_full_name, a2.full_name AS account_receiver_full_name FROM conversations c JOIN accounts a ON a.id = c.account_sender_id JOIN accounts a2 ON a2.id = c.account_receiver_id WHERE c.account_sender_id = ? OR c.account_receiver_id = ? GROUP BY c.id');
  $stmt->execute([ $_SESSION['account_id'], $_SESSION['account_id'] ]);
  $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
  // Sort the conversations by the most recent message date
  usort($conversations, function($a, $b) {
      $date_a = strtotime($a['msg_date'] ? $a['msg_date'] : $a['submit_date']);
      $date_b = strtotime($b['msg_date'] ? $b['msg_date'] : $b['submit_date']);
      return $date_b - $date_a;
  });
  
  // Conversations template below
?>
<div class="chat-widget-conversations">
  <a href="#" class="chat-widget-new-conversation" data-id="<?=$conversation['id']?>">&plus; New Chat</a>
  <?php foreach ($conversations as $conversation): ?>
    <a href="#" class="chat-widget-conversation" data-id="<?=$conversation['id']?>">
      <div class="icon" <?='style="background-color: ' . color_from_string($conversation['account_sender_id'] != $_SESSION['account_id'] ? $conversation['account_sender_full_name'] : $conversation['account_receiver_full_name']) . '"'?>><?=substr($conversation['account_sender_id'] != $_SESSION['account_id'] ? $conversation['account_sender_full_name'] : $conversation['account_receiver_full_name'], 0, 1)?></div>
      <div class="details">
        <div class="title"><?=htmlspecialchars($conversation['account_sender_id'] != $_SESSION['account_id'] ? $conversation['account_sender_full_name'] : $conversation['account_receiver_full_name'], ENT_QUOTES)?></div>
        <div class="msg"><?=htmlspecialchars($conversation['msg'], ENT_QUOTES)?></div>
      </div>
      <?php if ($conversation['msg_date']): ?>
        <div class="date"><?=date('Y/m/d') == date('Y/m/d', strtotime($conversation['msg_date'])) ? date('H:i', strtotime($conversation['msg_date'])) : date('d/m/y', strtotime($conversation['msg_date']))?></div>
      <?php else: ?>
        <div class="date"><?=date('Y/m/d') == date('Y/m/d', strtotime($conversation['submit_date'])) ? date('H:i', strtotime($conversation['submit_date'])) : date('d/m/y', strtotime($conversation['submit_date']))?></div>
      <?php endif; ?>
    </a>
  <?php endforeach; ?>
</div>