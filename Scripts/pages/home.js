  const contactForm = document.getElementById("contactForm");
  const submitBtn = document.getElementById("submitBtn");

  contactForm.addEventListener("submit", function (e) {
    e.preventDefault(); // prevent default form submission
    const formData = new FormData(contactForm);

    // Disable button + show loading state
    submitBtn.disabled = true;
    const originalText = submitBtn.textContent;
    submitBtn.textContent = "Sending...";

    fetch("../backend/mail.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showModal("Your message has been sent!", "success");
          contactForm.reset();
        } else {
          showModal(data.message || "Your message did not send!", "error");
        }
      })
      .catch(err => {
        console.error(err);
        showModal("Network error. Please try again.", "error");
      })
      .finally(() => {
        // Restore button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      });
  });