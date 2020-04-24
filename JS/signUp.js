function makeRequest() {
    return new Promise(function (resolve) {
        let userName = document.getElementById("usernameInput").value;
        let password = document.getElementById("passwordInput").value;
        let dataString = 'username=' + userName + '&password=' +password + '&method=SIGN_UP';
        let xhr = new XMLHttpRequest();
        let pathName=window.location.pathname; 
        let directory = pathName.substring(0, pathName.lastIndexOf('/'));
        directory =  directory.substring(0, directory.lastIndexOf('/'));
        let newPage  = directory + "/SERVER/crud.php";
        xhr.open('POST', newPage+'?'+dataString, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(dataString);
    });
}
async function waitForResponse() {
    let result = await makeRequest();
    return result;
}
async function registerUser()
{

    let userName = document.getElementById("usernameInput").value;
    let password = document.getElementById("passwordInput").value;
    let passwordCheck = document.getElementById("passwordCheck").value;
    if(password.length<5)
    {
        alert("You must enter a password with minimum of 5 characters.");
        return true;
    }
    else if(password!=passwordCheck)
   {
        alert("Both password don't match!");
        return true ;
   }
    let jsonResponse = await waitForResponse();
    let response = JSON.parse(jsonResponse);
    if(response.status=='1'){
        alert("User already exists");
        return true;
    }
    else{
        alert(response.status);
        let pathName=window.location.pathname; 
        let directory = pathName.substring(0, pathName.lastIndexOf('/'));
        directory = directory.substring(0, directory.lastIndexOf('/'));
        let newPage  = directory + "/HTML/registrationConfirmed.html";
        alert(newPage);
        window.location.href= newPage;
        alert(jsonResponse);
        return false;
    }
}
