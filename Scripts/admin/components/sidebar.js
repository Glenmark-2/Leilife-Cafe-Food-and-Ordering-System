function toggleSidebar() {
  const sidebar = document.getElementById("db-container");
  sidebar.classList.toggle("show");

  // When sidebar is shown, add a one-time click listener to close it when clicking outside
  if (sidebar.classList.contains("show")) {
    document.addEventListener("click", handleOutsideClick);
  }
}

// Handle clicks outside the sidebar to close it
function handleOutsideClick(event) {
  const sidebar = document.getElementById("db-container");
  const hamburger = document.getElementById("hamburger"); // assuming you have this ID on your button

  // If click is outside sidebar and hamburger, hide sidebar
  if (!sidebar.contains(event.target) && !hamburger.contains(event.target)) {
    sidebar.classList.remove("show");
    document.removeEventListener("click", handleOutsideClick);
  }
}

// Highlight active sidebar button
document.querySelectorAll('.sidebar-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.sidebar-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
});
