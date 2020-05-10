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
    var urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('code')!=null){
        if(confirm("Upload directory?")){
            changeStatusOneDriveDirectoryUpload();
        }
        else{
            changeStatusOneDrive();
        }
    }else{
        response = await waitForResponse('Code','OneDrive');
        location.assign(response);
    }
  
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
    let allCloudMethods = document.getElementById("allCloudMethods");
    var htmlString = "";
    htmlString = "<input id='fileid' type='file' hidden/>";
    googleDrive.insertAdjacentHTML('beforebegin',htmlString);
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
            let response = JSON.parse(responseJson);
            if(response.status=='401'){
                alert("Authorization failed");
            }
        } 
        else
        {
            if(urlParams.get('code')[0] == 'M'){
                var responseJson2 = await waitForResponse('Token','OneDrive');
            } else {
                var responseJson2 = await waitForResponse('Token','DropBox');
            }
            let response = JSON.parse(responseJson2);
             if(response.status=='401'){
                alert("Authorization failed");
            }
        } 
    } 
}
function changeStatusOneDrive(){
    var fileInput = document.getElementById('fileOneDrive');
    fileInput.click();
}
 function changeStatusOneDriveDirectoryUpload(){
    var directoryInput = document.getElementById('directoryOneDrive');
    directoryInput.click();
 }
 function makeRequestForFileTransfer(fileData){
    return new Promise(function (resolve) {
       let xhr = new XMLHttpRequest();
       xhr.open('POST', 'transferFile?fileData='+fileData, true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function responseForFileTransfer(fileData){
    let result = await makeRequestForFileTransfer(fileData);
    console.log(result);
    return result;
}
document.getElementById("fileOneDrive").addEventListener("change", async function(){
    if (this.files && this.files[0]) {
        for (var i = 0; i < this.files.length; i++) {
            var myFile = this.files[i];
            var reader = new FileReader();
            reader.addEventListener('load',  async function (e) {
                var arrayBuffer = this.result,
                array = new Uint8Array(arrayBuffer),
                binaryString = String.fromCharCode.apply(null, array);
                let result   = await responseForFileTransfer(binaryString);
                console.log(result);
            });
            reader.readAsArrayBuffer(myFile);
            //reader.readAsDataURL(myFile);
            //reader.readAsBinaryString(myFile);
        }
      }   
    } 
 );
function getDirectory(){
    var chooser = document.getElementById('directoryOneDrive');
    if ('files' in chooser) { 
        for (var i = 0; i < chooser.files.length; i++) {
            var file = chooser.files[i];
            if ('name' in file) {
                console.log(file.name);
            }
            if ('size' in file) {
                console.log(file.size);
            }
        }
    }
}
//https://www.w3schools.com/jsref/tryit.asp?filename=tryjsref_fileupload_files
//https://stackoverflow.com/questions/16210231/how-can-i-upload-a-new-file-on-click-of-image-button
//https://codepen.io/monjer/pen/JKRLzM
checkUrl();
/*https://stackoverflow.com/questions/32556664/getting-byte-array-through-input-type-file/32556944 */
/*https://stackoverflow.com/questions/14446447/how-to-read-a-local-text-file */
/*https://stackoverflow.com/questions/32556664/getting-byte-array-through-input-type-file/32556944 */
