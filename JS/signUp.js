function registerUser()
{
    let pathName=window.location.pathname; 
    let directory = pathName.substring(0, pathName.lastIndexOf('/'));
    let newPage  = directory + "/registrationConfirmed.html";
    let userName = document.getElementById("usernameInput").value;
    let password = document.getElementById("passwordInput").value;
    let passwordCheck = document.getElementById("passwordCheck").value;
    window.location.href= newPage;
    return false;
}
