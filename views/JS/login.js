
function makeRequest() {
    return new Promise(function (resolve) {
        let userName = document.getElementById("usernameInput").value;
        let passwordInput = document.getElementById("passwordInput").value;
        let dataString = 'username=' + userName + '&password=' +passwordInput + '&method=CHECK_USER_IN_DB';
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'login?'+dataString, true);
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
async function goToIndex(callback)
{
    /*https://stackoverflow.com/questions/48327559/save-async-await-response-on-a-variable*/
    /*https://dev.to/shoupn/javascript-fetch-api-and-using-asyncawait-47mp*/
    /*https://stackoverflow.com/questions/48969495/in-javascript-how-do-i-should-i-use-async-await-with-xmlhttprequest*/
    let jsonResponse = await waitForResponse();
    let response = JSON.parse(jsonResponse);
    if(response.status == '1'){
        alert("User is not valid");
        location.assign('login');
        return true;
    }else if(response.status== '0'){
        alert("You logged well. You will be redirected to the next page.");
        location.assign('home');
        return false;
    }
    else if(response.status== '2'){
        alert("Password is not corect");
        location.assign('login');
        return true;
    }
}
function goToSignUp()
{
    location.assign('signUp');
}
