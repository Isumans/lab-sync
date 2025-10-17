
function showAlertAndSubmit(event) {
     if (confirm("Are you sure you want to submit the form?")) {
        
}else{
    event.preventDefault(); 
    alert("Form submission cancelled."); 
}
}