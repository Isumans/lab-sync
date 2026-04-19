(function () {
  var containers = document.querySelectorAll('.navbar-notifications');

  function init(container) {
    var toggle = container.querySelector('.notification-toggle');
    var dropdown = container.querySelector('.notification-dropdown');

    if (!toggle || !dropdown) {
      return;
    }

    function setOpen(isOpen) {
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      dropdown.hidden = !isOpen;
    }

    setOpen(false);

    toggle.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      var isOpen = toggle.getAttribute('aria-expanded') === 'true';
      setOpen(!isOpen);
    });

    dropdown.addEventListener('click', function (event) {
      if (event.target.closest('.notification-item')) {
        setOpen(false);
      }
    });

    document.addEventListener('click', function (event) {
      if (!container.contains(event.target)) {
        setOpen(false);
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        setOpen(false);
      }
    });
  }

  for (var i = 0; i < containers.length; i++) {
    init(containers[i]);
  }
})();
