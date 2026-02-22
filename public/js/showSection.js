function showSection(sectionId, tabElement, event) {
            event.preventDefault(); // Prevent link jump

            // Hide all sections
            document.querySelectorAll('.content-area .section').forEach(function(sec) {
                sec.style.display = 'none';
            });
            // Show the selected section
            document.getElementById(sectionId).style.display = 'block';

            // Remove 'active' from all tabs
            document.querySelectorAll('.team-tab').forEach(function(tab) {
                tab.classList.remove('active');
            });
            // Add 'active' to the clicked tab
            tabElement.classList.add('active');
}

document.querySelectorAll('.sidebar a').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelectorAll('.sidebar a').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});