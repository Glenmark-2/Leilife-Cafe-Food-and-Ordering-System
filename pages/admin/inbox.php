<?php
// Sample inbox data
$inbox = [
    [
        "sender" => "Google",
        "email" => "bastagoogleaccgnagfeedback@",
        "subject" => "You Google Account was recovered successf..",
        "date" => "Sep 3",
        "message" => "The unli wings were amazing! Garlic Parmesan was my favorite, crispy and flavorful. The staff was friendly and always ready to refill. Worth the price!",
        "unread" => true
    ],
    [
        "sender" => "Google",
        "email" => "support@google.com",
        "subject" => "You Google Account was recovered successf..",
        "date" => "Sep 3",
        "message" => "Your account has been recovered successfully.",
        "unread" => true
    ],
    [
        "sender" => "Google",
        "email" => "noreply@google.com",
        "subject" => "You Google Account was recovered successf..",
        "date" => "Sep 3",
        "message" => "Another sample feedback message...",
        "unread" => false
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inbox System</title>
<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        height: 100vh;
        display: flex;
        background: #f7f7f7;
    }

    /* MAIN CONTAINER - inbox and feedback side by side */
    .main {
    display: flex;
    flex: 1;
    height: 100vh; /* make sure it fills the screen height */
        
}

    /* LEFT PANEL (Inbox) */
   .inbox-panel {
    width: 360px;
    background: #e0e0e0;
    border-right: 1px solid #ccc;
    display: flex;
    flex-direction: column;
    height: 100%;   /* fixed height */

}
    /* New header container */
.inbox-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 10px;
    border-bottom: 1px solid #ccc;
    background: #fff;
}

/* Title */


.search-bar {
        display: flex;
        align-items: center;
        padding: 10px;
        background: #fff;
        border-bottom: 1px solid #ccc;
    }
    .search-bar input {
        flex: 1;
        padding: 6px 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        outline: none;
    }
    .search-bar button {
        background: none;
        border: none;
        font-size: 16px;
        margin-left: 6px;
        cursor: pointer;
    }
    .inbox-title {
        padding: 10px;
        font-size: 14px;
        font-weight: bold;
    }
    .actions {
        display: flex-inline;
        gap: 10px;
        padding: 0;
    }
    .actions button {
        flex: 1;
        padding: 2px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background: #fff;
        cursor: pointer;
        font-size: 11px;
    }
    .mail-list {
        flex: 1;
        overflow-y: auto;
        background: #e0e0e0;
    }
    .mail-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border-bottom: 1px solid #ccc;
        cursor: pointer;
    }
    .mail-item:hover {
        background: #dcdcdc;
    }
    .checkbox {
        width: 16px;
        height: 16px;
    }
    .unread-dot {
        width: 10px;
        height: 10px;
        background: #007bff;
        border-radius: 50%;
    }
    .mail-avatar {
        width: 32px;
        height: 32px;
        background: #ddd;
        border-radius: 50%;
    }
    .mail-info {
        flex: 1;
    }
    .mail-sender {
        font-weight: bold;
        font-size: 14px;
    }
    .mail-subject {
        font-size: 12px;
        color: #555;
    }
    .mail-date {
        font-size: 12px;
        color: #777;
    }

    /* RIGHT PANEL (Feedback) */
  /* RIGHT PANEL (Feedback) */
.feedback-panel {
    width: 700px; /* fixed width */
    background: #fff;
    display: flex;
    flex-direction: column;
    height: 100%;   /* fixed height */
}

/* Make message area scroll if content is too long */
.feedback-message {
    margin: 20px;
    font-size: 15px;
    line-height: 1.5;
    overflow-y: auto;
    flex: 1; /* fill remaining vertical space */
}





    .header {
        display: flex;
        justify-content: flex-end;
        padding: 10px 20px;
        border-bottom: 1px solid #ccc;
    }
    .profile {
        font-size: 14px;
        background: #eee;
        padding: 5px 10px;
        border-radius: 20px;
        cursor: pointer;
    }
    .feedback-from {
        margin: 20px;
        font-weight: bold;
        font-size: 16px;
    }
    .feedback-user {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 20px 10px;
    }
    .feedback-avatar {
        width: 40px;
        height: 40px;
        background: #ddd;
        border-radius: 50%;
    }
    .feedback-details {
        display: flex;
        flex-direction: column;
    }
    .feedback-email {
        font-size: 13px;
        color: #777;
    }
   
</style>
<script>
    function showMessage(index) {
        let inbox = <?php echo json_encode($inbox); ?>;
        document.getElementById("feedbackFrom").innerText = inbox[index].sender;
        document.getElementById("feedbackEmail").innerText = inbox[index].email;
        document.getElementById("feedbackMessage").innerText = inbox[index].message;
    }
</script>
</head>
<body>

<div class="main">
    <!-- LEFT Inbox Panel -->
    <div class="inbox-panel">
        <div class="search-bar">
            <input type="text" placeholder="Search">
            <button>🎤</button>
        </div>
        <div class="inbox-header">
    <div class="inbox-title">All Inbox</div>
    <div class="actions">
        <button>Mark as read</button>
        <button>Delete</button>
    </div>
</div>

        <div class="mail-list">
            <?php foreach ($inbox as $index => $mail): ?>
            <div class="mail-item" onclick="showMessage(<?php echo $index; ?>)">
                <input type="checkbox" class="checkbox">
                <?php if ($mail['unread']): ?>
                    <div class="unread-dot"></div>
                <?php else: ?>
                    <div style="width:10px; height:10px;"></div>
                <?php endif; ?>
                <div class="mail-avatar"></div>
                <div class="mail-info">
                    <div class="mail-sender"><?php echo $mail['sender']; ?></div>
                    <div class="mail-subject"><?php echo $mail['subject']; ?></div>
                </div>
                <div class="mail-date"><?php echo $mail['date']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- RIGHT Feedback Panel -->
    <div class="feedback-panel">
        <div class="feedback-from">Feedback From:</div>
        <div class="feedback-user">
            <div class="feedback-avatar"></div>
            <div class="feedback-details">
                <span id="feedbackFrom"><?php echo $inbox[0]['sender']; ?></span>
                <span class="feedback-email" id="feedbackEmail"><?php echo $inbox[0]['email']; ?></span>
            </div>
        </div>
        <hr>
        <p class="feedback-message" id="feedbackMessage"><?php echo $inbox[0]['message']; ?></p>
    </div>
</div>

</body>
</html>
