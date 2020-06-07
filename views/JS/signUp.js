function makeRequest() {
    return new Promise(function (resolve) {
        let userName = document.getElementById("usernameInput").value;
        let password = document.getElementById("passwordInput").value;
        let dataString = 'username=' + userName + '&password=' +password + '&method=SIGN_UP';
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'signUp?'+dataString, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function waitForResponse() {
    let result = await makeRequest();
    return result;
}
async function registerUser()
{
    let password = document.getElementById("passwordInput").value;
    let passwordCheck = document.getElementById("passwordCheck").value;
    if(password.length<5)
    {
        alert("You must enter a password with minimum of 5 characters.");
        location.assign('signUp');
    }
    else if(password!=passwordCheck)
   {
        alert("Both passwords don't match!");
        location.assign('signUp');
   }
    let jsonResponse = await waitForResponse();
    let response = JSON.parse(jsonResponse);
    if(response.status=='1'){
        location.assign('registrationConfirmed');
    } else if(response.status == '3'){
        alert("User already exists");
        location.assign('signUp');
    }
}
