function makeRequestForCode() {
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'getCode', true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
function makeRequestForToken(code){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
       xhr.open('GET', 'getToken?code='+code, true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function waitForResponse(reason) {
    if(reason=='Code'){
        let result = await makeRequestForCode();
        return result;
    }else if(reason=='Token'){
        var urlParams = new URLSearchParams(window.location.search);
        let result = await makeRequestForToken(urlParams.get('code'));
        return result;
    }
    
}
 async function uploadAllDrivers()
{
    let oneDrive = document.getElementById("one-drive");
    let googleDrive = document.getElementById("google-drive");
    let dropBox = document.getElementById("drop-box");
    let allCloudMethods = document.getElementById("allCloudMethods");

    var htmlString = "";
    htmlString = "<input id='fileid' type='file' hidden/>";
    googleDrive.insertAdjacentHTML('beforebegin',htmlString);

    allCloudMethods.addEventListener('click', function(){
        document.getElementById('fileid').click();
    });
    oneDrive.addEventListener('click', async function(){
        //document.getElementById('fileid').click();
        response = await waitForResponse('Code');
        location.assign(response);
    });
    googleDrive.addEventListener('click', function(){
        document.getElementById('fileid').click();
    });
    dropBox.addEventListener('click', function(){
        document.getElementById('fileid').click();
    });
}
function openMenu()
{
    document.getElementById("shownMenu").removeAttribute("hidden");
    document.getElementById("shownMenu").style.width = "250px";
    document.getElementById("sideMenu").style.marginLeft = "250px";
}
function closeMenu()
{
    document.getElementById("shownMenu").style.width = "0";
    document.getElementById("sideMenu").style.marginLeft= "0";
}
function logOutUser(){
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'logOut', true);
    alert("Your session has ended.");
    xhr.send();
    location.assign('login');
}
async function checkURl(){
    var urlParams = new URLSearchParams(window.location.search); 
    if(urlParams.get('code')!=null)
    {
        let responseJson = await waitForResponse('Token');
        alert(responseJson);
        let response = JSON.parse(responseJson);
        if(response.status =='200'){
            alert("Ok");
        }else if(response.status=='401'){
            alert("Authorization failed");
        }
    } 
}
checkURl();