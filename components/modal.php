<?php
if (!function_exists('createModal')) {
    function createModal(string $defaultMessage = '', string $defaultType = 'success') {
        $escapedMessage = htmlspecialchars($defaultMessage, ENT_QUOTES);
        $escapedType = htmlspecialchars($defaultType, ENT_QUOTES);

        return <<<HTML
<script>
function showModal(message = "{$escapedMessage}", type = "{$escapedType}", autoClose = true, duration = 3000) {
    let modal = document.getElementById("notif-modal");
    if (!modal) {
        modal = document.createElement("div");
        modal.id = "notif-modal";
        modal.className = "notif-modal";
        modal.innerHTML = `
        <div class="notif-content">
            <p id="notif-message"></p>
            <button id="notif-close">OK</button>
        </div>`;
        document.body.appendChild(modal);

        const style = document.createElement("style");
        style.innerHTML = `
        .notif-modal { display:none; position:fixed; z-index:10000; left:0; top:0;
                        width:100%; height:100%; background:rgba(0,0,0,0.4);
                        justify-content:center; align-items:center; }
        .notif-content { background:white; padding:20px 30px; border-radius:10px;
                        text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.3);
                        min-width:250px; animation:popin .3s ease; }
        .notif-content p { margin-bottom:15px; font-size:16px; }
        .notif-content button { padding:6px 16px; border:none; border-radius:6px;
                                cursor:pointer; font-size:14px; color:white; }
        .notif-content button.success { background:#4caf50; }
        .notif-content button.error { background:#f44336; }
        .notif-content button.warning { background:#ff9800; }
        @keyframes popin { from{transform:scale(0.8);opacity:0;} to{transform:scale(1);opacity:1;} }
        `;
        document.head.appendChild(style);
    }

    document.getElementById("notif-message").textContent = message;
    const closeBtn = document.getElementById("notif-close");
    closeBtn.className = type;

    modal.style.display = "flex";
    const closeModal = () => modal.style.display = "none";
    closeBtn.onclick = closeModal;
    modal.onclick = (e) => { if (e.target === modal) closeModal(); };

    if (autoClose) setTimeout(closeModal, duration);
}
</script>
HTML;
    }
}
?>
