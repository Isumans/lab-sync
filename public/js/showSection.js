function showSection(sectionId, event) {
            event.preventDefault(); // Prevent link jump

            // Hide all sections
            document.querySelectorAll('#content-area .section').forEach(function(sec) {
                sec.style.display = 'none';
            });
            // Show the selected section
            document.getElementById(sectionId).style.display = 'block';

            // Remove 'active' from all navItems
            document.querySelectorAll('.navItem').forEach(function(item) {
                item.classList.remove('active');
            });
            // Add 'active' to the clicked navItem
            event.target.closest('.navItem').classList.add('active');
            // Optionally, show the corresponding section
            document.getElementById(sectionId).style.display = 'block';
    
}

document.querySelectorAll('.sidebar a').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelectorAll('.sidebar a').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});