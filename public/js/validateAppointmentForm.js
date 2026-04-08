// Deprecated: validation moved to appointmentPopup.js
// This file remains as a backward-compatible shim.
(function(){
  if (window.ensurePatientSelected) return; // already provided by merged script
  window.ensurePatientSelected = function(){
    console.warn('ensurePatientSelected: deprecated shim; please include appointmentPopup.js');
    var pidEl = document.getElementById('patient_id');
    var pid = pidEl ? pidEl.value.trim() : '';
    if(!pid){
      alert('Please select an existing patient from the suggestions before submitting the appointment.');
      var search = document.getElementById('patient-search');
      if(search) search.focus();
      return false;
    }
    return true;
  };
})();
