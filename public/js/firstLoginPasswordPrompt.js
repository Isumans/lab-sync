(function () {
  var modal = document.getElementById('firstLoginPasswordModal');
  if (!modal) {
    return;
  }

  var closeBtn = document.getElementById('firstLoginModalClose');
  var maybeLaterBtn = document.getElementById('firstLoginMaybeLater');
  var dismissUrl = modal.getAttribute('data-dismiss-url') || '';
  var csrf = modal.getAttribute('data-csrf') || '';
  var shouldOpen = modal.getAttribute('data-open') === '1';

  if (shouldOpen) {
    modal.classList.add('is-open');
  }

  function closeAndRemember() {
    modal.classList.remove('is-open');
    if (!dismissUrl || !csrf) {
      return;
    }

    fetch(dismissUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: 'csrf_token=' + encodeURIComponent(csrf)
    }).catch(function () {
      // No-op: failure to save reminder state should not block usage.
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', closeAndRemember);
  }

  if (maybeLaterBtn) {
    maybeLaterBtn.addEventListener('click', closeAndRemember);
  }
})();
