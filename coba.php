<?php
  include 'main.php';
  $stmt = $pdo->prepare('SELECT c.*, (SELECT msg FROM messages WHERE conversation_id = c.id ORDER BY submit_date DESC LIMIT 1) AS msg, (SELECT submit_date FROM messages WHERE conversation_id = c.id ORDER BY submit_date DESC LIMIT 1) AS msg_date, a.full_name AS account_sender_full_name, a2.full_name AS account_receiver_full_name FROM conversations c JOIN accounts a ON a.id = c.account_sender_id JOIN accounts a2 ON a2.id = c.account_receiver_id WHERE c.account_sender_id = ? OR c.account_receiver_id = ? GROUP BY c.id');
  $stmt->execute([ 2, 2 ]);
  $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
  // Sort the conversations by the most recent message date
  usort($conversations, function($a, $b) {
    $date_a = strtotime($a['msg_date'] ? $a['msg_date'] : $a['submit_date']);
    $date_b = strtotime($b['msg_date'] ? $b['msg_date'] : $b['submit_date']);
    $c = $date_b - $date_a;
    echo $c;
    return $c;
  });

  foreach($conversations as $conversation){
    echo "{$conversation['id']}  {$conversation['account_sender_id']} {$conversation['account_receiver_id']} {$conversation['submit_date']} {$conversation['msg']} {$conversation['msg_date']}";
    echo "<br>";
  }