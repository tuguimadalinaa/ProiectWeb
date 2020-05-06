function makeRequestForCode(drive) {
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'getCode?drive='+drive, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
function makeRequestForToken(code,drive){
    return new Promise(function (resolve) {
       let xhr = new XMLHttpRequest();
       xhr.open('GET', 'getToken?code='+code+'&drive='+drive, true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function waitForResponse(reason,drive) {
    if(reason=='Code'){
        let result = await makeRequestForCode(drive);
        return result;
    }else if(reason=='Token'){
        var urlParams = new URLSearchParams(window.location.search);
        let result = await makeRequestForToken(urlParams.get('code'),drive);
        return result;
    }
    
}

async function uploadOneDrive(){
    response = await waitForResponse('Code','OneDrive');
    location.assign(response);
}
async function uploadGoogleDrive(){
    response = await waitForResponse('Code','GoogleDrive');
    location.assign(response);
}

async function uploadDropBox(){
    response = await waitForResponse('Code','DropBox');
    location.assign(response);
}

 async function uploadAllDrivers()
{
    //let oneDrive = document.getElementById("one-drive");
    //let googleDrive = document.getElementById("google-drive");
    //let dropBox = document.getElementById("drop-box");
    let allCloudMethods = document.getElementById("allCloudMethods");
    var htmlString = "";
    htmlString = "<input id='fileid' type='file' hidden/>";
    googleDrive.insertAdjacentHTML('beforebegin',htmlString);

    //allCloudMethods.addEventListener('click', function(){
       // document.getElementById('fileid').click();
   // });
   // oneDrive.addEventListener('click', async function(){
        //document.getElementById('fileid').click();
        //response = await waitForResponse('Code','OneDrive');
       // location.assign(response);
    //});
    //googleDrive.addEventListener('click', function(){
       // document.getElementById('fileid').click();
    //});
    //dropBox.addEventListener('click', async function(){
        //document.getElementById('fileid').click();
        //response = await waitForResponse('Code','DropBox');
        //location.assign(response);
   //});
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
    xhr.send();
    location.assign('login');
}
async function checkUrl(){
    var urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('code')!=null){
        if(urlParams.get('scope')!=null)
        {
            let responseJson = await waitForResponse('Token','GoogleDrive'); 
            alert('GoogleDrive');
            let response = JSON.parse(responseJson);
            if(response.status =='200'){
                alert("Okey");
            }else if(response.status=='401'){
                alert("Authorization failed");
            }
        } 
        else
        {
            if(urlParams.get('code')[0] == 'M'){
                var responseJson2 = await waitForResponse('Token','OneDrive'); 
                alert('OneDrive'); 
            } else {
                var responseJson2 = await waitForResponse('Token','DropBox');
                alert('DropBox');  
            }
            let response = JSON.parse(responseJson2);
            if(response.status =='200'){
                alert("Okey");
            }else if(response.status=='401'){
                alert("Authorization failed");
            }
        } 
    } 
}
checkUrl();
