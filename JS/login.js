function goToIndex()
{
    let pathName=window.location.pathname; 
    let directory = pathName.substring(0, pathName.lastIndexOf('/'));
    let newPage  = directory + "/index.html";
    let userName = document.getElementById("usernameInput").value;
    let password = document.getElementById("passwordInput").value;
    window.location.href= newPage;
    alert(userName + "    " + password);
    return false;
    //https://stackoverflow.com/questions/16562577/how-can-i-make-a-button-redirect-my-page-to-another-page
    //https://stackoverflow.com/questions/3151436/how-can-i-get-the-current-directory-name-in-javascript
    //https://stackoverflow.com/questions/6109527/window-location-href-not-working-in-form-onsubmit
}