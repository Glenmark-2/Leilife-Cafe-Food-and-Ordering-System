document.addEventListener("DOMContentLoaded", () => {

     const reorderForm = document.getElementById("reorderForm");
        const reorderBtn = document.getElementById("reorderBtn");

        if (reorderForm && reorderBtn) {
            reorderBtn.addEventListener("click", async (e) => {
                e.preventDefault();

                const confirmed = await showConfirm(
                    "Reordering will remove all current items in your cart. Do you want to continue?"
                );

                if (!confirmed) return;

                const formData = new FormData(reorderForm);
                const orderId = formData.get("order_id");

                reorderBtn.disabled = true; // prevent double-clicks

                try {
                    const res = await fetch("../backend/reorder.php", {
                        method: "POST",
                        body: formData,
                    });

                    const text = await res.text();
                    console.log("Raw response:", text);

                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch {
                        throw new Error("Invalid JSON: " + text);
                    }

                    if (data.success) {
                        showModal(data.message || "Order reordered successfully!", "success", true, 2000);

                        // ✅ Use redirect path from PHP if provided
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1500);
                        }
                    } else {
                        showModal(data.message || "Failed to reorder", "error", true, 4000);
                    }
                } catch (err) {
                    console.error("Reorder error:", err);
                    showModal("Network error while reordering.", "error", true, 4000);
                } finally {
                    reorderBtn.disabled = false;
                }
            });
        }


            // ✅ Review form
        const reviewForm = document.getElementById("reviewForm");
        const submitBtn = document.getElementById("submitReviewBtn");
        if (reviewForm && submitBtn) {
            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const formData = new FormData(reviewForm);
                fetch("/leilife/backend/mail.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.text())
                    .then(text => {
                        console.log("Mail response:", text);
                        return JSON.parse(text);
                    })
                    .then(data => {
                        if (data.success) {
                            showModal(data.message, "success");
                            setTimeout(() => window.location.href = "/leilife/public/index.php?page=home", 2000);
                        } else showModal(data.message || "Your review did not send!", "error");
                    })
                    .catch(err => {
                        console.error("Fetch error:", err);
                        showModal("Network error. Please try again.", "error");
                    });
            });
        }



    
    
});

        // ✅ Confirmation modal built on top of showModal()
    function showConfirmModal(message, onConfirm) {
        let modal = document.getElementById("notif-modal");
        if (!modal) {
            showModal();
            modal = document.getElementById("notif-modal");
            modal.style.display = "none";
        }

        const content = modal.querySelector(".notif-content");
        const originalHTML = content.innerHTML;

        content.innerHTML = `
        <p>${message}</p>
        <div style="display:flex; justify-content:center; gap:10px;">
            <button id="confirm-yes" class="success">Yes</button>
            <button id="confirm-no" class="error">Cancel</button>
        </div>
    `;

        modal.style.display = "flex";
        document.getElementById("confirm-yes").onclick = () => {
            modal.style.display = "none";
            content.innerHTML = originalHTML;
            onConfirm();
        };
        document.getElementById("confirm-no").onclick = () => {
            modal.style.display = "none";
            content.innerHTML = originalHTML;
        };
    }

function showConfirm(message) {
        return new Promise((resolve) => {
            let modal = document.getElementById("confirm-modal");
            if (!modal) {
                modal = document.createElement("div");
                modal.id = "confirm-modal";
                modal.style.cssText = `
                display:none; position:fixed; z-index:10000; left:0; top:0;
                width:100%; height:100%; background:rgba(0,0,0,0.4);
                justify-content:center; align-items:center;
            `;
                modal.innerHTML = `
                <div class="confirm-content" style="
                    background:white; padding:20px 30px; border-radius:10px;
                    text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.3);
                    min-width:280px; animation:popin .3s ease;
                ">
                    <p id="confirm-message" style="margin-bottom:20px; font-size:16px;"></p>
                    <div style="display:flex; gap:15px; justify-content:center;">
                        <button id="confirm-yes" style="
                            padding:6px 16px; border:none; border-radius:6px;
                            cursor:pointer; font-size:14px; color:white; background:#4caf50;
                        ">Yes</button>
                        <button id="confirm-no" style="
                            padding:6px 16px; border:none; border-radius:6px;
                            cursor:pointer; font-size:14px; color:white; background:#f44336;
                        ">No</button>
                    </div>
                </div>
            `;
                document.body.appendChild(modal);
            }

            document.getElementById("confirm-message").textContent = message;
            const yesBtn = document.getElementById("confirm-yes");
            const noBtn = document.getElementById("confirm-no");

            modal.style.display = "flex";

            const closeModal = () => {
                modal.style.display = "none";
            };

            yesBtn.onclick = () => {
                closeModal();
                resolve(true);
            };
            noBtn.onclick = () => {
                closeModal();
                resolve(false);
            };
            modal.onclick = (e) => {
                if (e.target === modal) {
                    closeModal();
                    resolve(false);
                }
            };
        });
    }