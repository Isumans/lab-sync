function showSection(sectionId, tabElement, event) {
            event.preventDefault(); // Prevent link jump

    // Mapping of section IDs to their corresponding controller actions
    const sectionUrls = {
        'partner-labs': '/lab_sync/index.php?controller=partnerLabController&action=getPartnerLabsSection',
        'configuration': '/lab_sync/index.php?controller=administratorController&action=getLabConfigurationSection',
        'general': '/lab_sync/index.php?controller=administratorController&action=getGeneralSettingsSection'
    };

    const sectionElement = document.getElementById(sectionId);
    if (!sectionElement) {
        console.error('Section element not found:', sectionId);
        return;
    }

    console.log('Showing section:', sectionId);

    // Check if content needs to be loaded
    if (sectionUrls[sectionId] && sectionElement.innerHTML.trim() === '') {
        console.log('Fetching content for:', sectionId);
        // Show loading state
        sectionElement.innerHTML = '<div class="loading-spinner">Loading...</div>'; // basic loading indicator

        fetch(sectionUrls[sectionId])
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                sectionElement.innerHTML = html;
                // Re-initialize any scripts if necessary (e.g., event listeners)
            })
            .catch(error => {
                console.error('Error loading section:', error);
                sectionElement.innerHTML = '<div class="error-message">Error loading content. Please try again.</div>';
            });
    }

    // Hide all sections
    document.querySelectorAll('.content-area .section').forEach(function (sec) {
        sec.style.display = 'none';
    });
    // Show the selected section
    sectionElement.style.display = 'block';

    // Remove 'active' from all navItems
    document.querySelectorAll('.navItem').forEach(function (item) {
        item.classList.remove('active');
    });
    // Add 'active' to the clicked navItem
    if (event && event.target) {
        event.target.closest('.navItem').classList.add('active');
    } else {
        // Fallback or handle initial load if needed
        const link = document.querySelector(`.navItem[onclick*="'${sectionId}'"]`);
        if (link) link.classList.add('active');
    }

            // Remove 'active' from all tabs
            document.querySelectorAll('.team-tab').forEach(function(tab) {
                tab.classList.remove('active');
            });
            // Add 'active' to the clicked tab
            tabElement.classList.add('active');
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