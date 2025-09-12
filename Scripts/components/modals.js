const BASE_URL = "http://localhost/Leilife/";

function showModal(message, type = "success", autoClose = true, duration = 3000) {
  // check if modal already exists
  let modal = document.getElementById("notif-modal");
  if (!modal) {
    modal = document.createElement("div");
    modal.id = "notif-modal";
    modal.className = "notif-modal";
    modal.innerHTML = `
      <div class="notif-content">
        <p id="notif-message"></p>
        <button id="notif-close">OK</button>
      </div>
    `;
    document.body.appendChild(modal);

    // basic styles
    const style = document.createElement("style");
    style.innerHTML = `
      .notif-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.4);
        justify-content: center;
        align-items: center;
      }
      .notif-content {
        background: white;
        padding: 20px 30px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        min-width: 250px;
        animation: popin 0.3s ease;
      }
      .notif-content p { margin-bottom: 15px; font-size: 16px; }
      .notif-content button {
        padding: 6px 16px; border: none; border-radius: 6px;
        cursor: pointer; font-size: 14px; color: white;
      }
      .notif-content button.success { background: #4caf50; }
      .notif-content button.error { background: #f44336; }
      @keyframes popin {
        from { transform: scale(0.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
      }
    `;
    document.head.appendChild(style);
  }

  // set message
  document.getElementById("notif-message").textContent = message;

  // set button color based on type
  const closeBtn = document.getElementById("notif-close");
  closeBtn.className = type === "success" ? "success" : "error";

  // show modal
  modal.style.display = "flex";

  // close handlers
  const closeModal = () => modal.style.display = "none";
  closeBtn.onclick = closeModal;
  modal.onclick = (e) => { if (e.target === modal) closeModal(); };

  // auto close after duration
  if (autoClose) {
    setTimeout(() => {
      closeModal();
    }, duration);
  }
}