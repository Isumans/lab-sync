(function () {
  const modals = [];
  let activeTrigger = null;

  const setModalOpen = function (modalConfig, open) {
    const modal = modalConfig.modal;
    const form = modalConfig.form;

    modal.classList.toggle('is-open', open);
    modal.setAttribute('aria-hidden', open ? 'false' : 'true');
    const hasOpenModal = modals.some(function (entry) {
      return entry.modal.classList.contains('is-open');
    });
    document.body.style.overflow = open || hasOpenModal ? 'hidden' : '';

    if (open) {
      const firstField = form.querySelector('input:not([readonly]), select, textarea');
      if (firstField) {
        firstField.focus();
      }
      return;
    }

    if (activeTrigger) {
      activeTrigger.focus();
      activeTrigger = null;
    }
  };

  const registerModal = function (config) {
    const modal = document.getElementById(config.modalId);
    const openBtn = document.getElementById(config.openBtnId);
    const closeBtn = document.getElementById(config.closeBtnId);
    const cancelBtn = document.getElementById(config.cancelBtnId);
    const form = document.getElementById(config.formId);

    if (!modal || !openBtn || !closeBtn || !cancelBtn || !form) {
      return;
    }

    const modalConfig = {
      modal: modal,
      form: form,
    };

    modals.push(modalConfig);

    openBtn.addEventListener('click', function () {
      activeTrigger = openBtn;
      setModalOpen(modalConfig, true);
    });

    closeBtn.addEventListener('click', function () {
      setModalOpen(modalConfig, false);
    });

    cancelBtn.addEventListener('click', function () {
      setModalOpen(modalConfig, false);
    });

    modal.addEventListener('click', function (event) {
      if (event.target === modal) {
        setModalOpen(modalConfig, false);
      }
    });
  };

  registerModal({
    modalId: 'profileEditModal',
    openBtnId: 'openProfileEditModal',
    closeBtnId: 'profileEditClose',
    cancelBtnId: 'profileEditCancel',
    formId: 'profileEditForm',
  });

  registerModal({
    modalId: 'passwordChangeModal',
    openBtnId: 'openPasswordModal',
    closeBtnId: 'passwordChangeClose',
    cancelBtnId: 'passwordChangeCancel',
    formId: 'passwordChangeForm',
  });

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
      return;
    }

    const openModal = modals.find(function (entry) {
      return entry.modal.classList.contains('is-open');
    });

    if (openModal) {
      setModalOpen(openModal, false);
    }
  });
})();
