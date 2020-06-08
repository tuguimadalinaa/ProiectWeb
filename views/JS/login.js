
function makeRequest() {
    return new Promise(function (resolve) {
        let userName = document.getElementById("usernameInput").value;
        let passwordInput = document.getElementById("passwordInput").value;
        let dataString = 'username=' + userName + '&password=' +passwordInput + '&method=CHECK_USER_IN_DB';
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'login?'+dataString, false);
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
async function goToIndex(callback)
{
    let jsonResponse = await waitForResponse(true);
    alert(jsonResponse);
    let response = JSON.parse(jsonResponse);
    if(response.status == '1'){
        alert("Username is wrong");
        location.assign('login');
    }else if(response.status== '0'){
        alert("You logged well");
        location.assign('home');
    }
    else if(response.status== '2'){
        alert("Password is wrong");
        location.assign('login');
    }
}
function goToSignUp()
{
    location.assign('signUp');
}
