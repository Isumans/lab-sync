function setActiveLink(link) {
    const links = document.querySelectorAll('.sidebar a');
    links.forEach(l => l.classList.remove('active'));
    link.classList.add('active');
    
}