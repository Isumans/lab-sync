function showSection(sectionId, event) {
    event.preventDefault(); // Prevent link jump

    // Hide all sections
    document.querySelectorAll('.content-area .section').forEach(function (sec) {
        sec.style.display = 'none';
    });
    // Show the selected section
    document.getElementById(sectionId).style.display = 'block';

    // Remove 'active' from all navItems
    document.querySelectorAll('.navItem').forEach(function (item) {
        item.classList.remove('active');
    });
    // Add 'active' to the clicked navItem
    event.target.closest('.navItem').classList.add('active');
    // Optionally, show the corresponding section
    // document.getElementById(sectionId).style.display = 'block';

}

document.querySelectorAll('.sidebar a').forEach(link => {
    link.addEventListener('click', function () {
        document.querySelectorAll('.sidebar a').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const section = urlParams.get('section');
    if (section) {
        // Find the link that corresponds to this section
        const link = document.querySelector(`.navItem[onclick*="'${section}'"]`);
        if (link) {
            // Simulate click or manually trigger showSection
            showSection(section, { preventDefault: () => { }, target: link });
        }
    } else {
        // Default to first section or 'team' if no section specified
        // Or do nothing if PHP/HTML handles default visibility
        // Based on previous code, team is default if nothing else is shown,
        // but let's ensure 'team' is active if no section is active?
        // Actually, let's keep it simple. If section is present, switch to it.
    }
});