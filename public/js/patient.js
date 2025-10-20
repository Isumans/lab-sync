// This file is intentionally tiny for now. Keep shared UI helpers here if needed.
// Example: close modal when pressing ESC
document.addEventListener('keydown', e=>{
  if(e.key==='Escape') document.querySelectorAll('.modal.open').forEach(m=>m.classList.remove('open'));
});
