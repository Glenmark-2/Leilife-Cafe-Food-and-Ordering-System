<?php
// Sample inbox data
$inbox = [
    [
        "sender" => "Google",
        "email" => "bastagoogleaccgnagfeedback@",
        "subject" => "You Google Account was recovered successfuly",
        "date" => "Sep 3",
        "message" => "The unli wings were amazing! Garlic Parmesan was my favorite, crispy and flavorful. The staff was friendly and always ready to refill. Worth the price!",
        "unread" => false
    ],
    [
        "sender" => "Google",
        "email" => "support@google.com",
        "subject" => "You Google Account was recovered successfully",
        "date" => "Sep 3",
        "message" => "The unli wings were amazing! Garlic Parmesan was my favorite, crispy and flavorful. The staff was friendly and always ready to refill. Worth the price!",
        "unread" => true
    ],
    [
        "sender" => "Google",
        "email" => "noreply@google.com",
        "subject" => "You Google Account was recovered successf..",
        "date" => "Sep 3",
        "message" => "The unli wings were amazing! Garlic Parmesan was my favorite",
        "unread" => false
    ]
];
?>
<body>

<div id="first-row">
    <h2>Inbox</h2>
</div>

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
        <div class="feedback-header">
            <button class="feedback-close" onclick="closeFeedback()">Close</button>
        </div>  
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

<script>
    function showMessage(index) {
        let inbox = <?php echo json_encode($inbox); ?>;
        document.getElementById("feedbackFrom").innerText = inbox[index].sender;
        document.getElementById("feedbackEmail").innerText = inbox[index].email;
        document.getElementById("feedbackMessage").innerText = inbox[index].message;

        // Show feedback panel on mobile
        if (window.innerWidth <= 768) {
            document.querySelector(".feedback-panel").classList.add("active");
        }
    }

    function closeFeedback() {
        document.querySelector(".feedback-panel").classList.remove("active");
    }


</script>

