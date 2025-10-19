    function toggleSidebar() {
    const sidebar = document.getElementById("db-container");
    sidebar.classList.toggle("collapsed");
}


    document.querySelectorAll('.sidebar-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // remove active class from all buttons
            document.querySelectorAll('.sidebar-btn').forEach(b => b.classList.remove('active'));
            // add active to the clicked one
            this.classList.add('active');
        });
    });