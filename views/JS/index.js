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

function makeRequestForUpload(drive){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        if(drive == 'OneDrive'){

        } else if(drive == 'DropBox'){
             xhr.open('GET', 'uploadDropbox', true);
        } else if(drive == 'GoogleDrive'){
            xhr.open('GET', 'uploadGoogleDrive', true);
        }
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
        //alert(result);
        return result;
    }else if(reason=='Token'){
        var urlParams = new URLSearchParams(window.location.search);
        let result = await makeRequestForToken(urlParams.get('code'),drive);
        return result;
    } else if(reason == 'upload'){
        let result = await makeRequestForUpload(drive);
        return result;
    }
}

async function uploadOneDrive(){
    response = await waitForResponse('Code','OneDrive');
    location.assign(response);
  
}
async function uploadGoogleDrive(){
    response = await waitForResponse('Code','GoogleDrive');
    //alert(response);
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
    //location.assign('login');  //logOut imi merge fara location.assign(), ma redirecteaza la pagina de login din routes.php la ruta /home (Robert)
}
async function checkUrl(){
    var urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('code')!=null){
        if(urlParams.get('scope')!=null)
        {
            let responseJson = await waitForResponse('Token','GoogleDrive'); 
            //console.log(responseJson);
            //alert(responseJson);
            let response = JSON.parse(responseJson);
            alert("Authorization granted");
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
            //alert(response);
            /*if(response.status=='401'){
                alert("Authorization failed");
            }*/
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
 function makeRequestForFileTransfer(fileData,fileName,fileSize){
    return new Promise(function (resolve) {
       let xhr = new XMLHttpRequest();
       xhr.open('POST', 'transferFile?fileTransfName='+fileName + '&fileSize='+fileSize, true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(fileData);
    });
}
async function responseForFileTransfer(fileData, fileName,fileSize){
    let result = await makeRequestForFileTransfer(fileData,fileName,fileSize);
    return result;
}
function makeRequestForUploadingSmallItemAPI(file){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        fileArgs = JSON.stringify({ name: file.name });
        xhr.open('POST', 'APIuploadFinish', true);
        xhr.setRequestHeader('File-Args',fileArgs);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(file);
    });
}
function makeRequestForUploadSessionStartAPI(fileSlice,fileName){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        fileArgs = JSON.stringify({ name: fileName });
        xhr.open('POST', 'APIuploadStart', true);
        xhr.setRequestHeader('File-Args',fileArgs);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(fileSlice);
    });
}
function makeRequestForUploadSessionAppendAPI(fileSlice,fileName){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        fileArgs = JSON.stringify({ name: fileName });
        xhr.open('POST', 'APIuploadAppend', true);
        xhr.setRequestHeader('File-Args',fileArgs);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(fileSlice);
    });
}
function makeRequestForUploadSessionFinishAPI(fileSlice,fileName){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        fileArgs = JSON.stringify({ name: fileName });
        xhr.open('POST', 'APIuploadFinish', true);
        xhr.setRequestHeader('File-Args',fileArgs);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(fileSlice);
    });
}
async function prepareUpload(files){
    let numberOfFilesToUpload = files.files.length;
    let i = 0;
    let maxUploadSize = 1048576 * 40; // aprox 40 MB
    while(i < numberOfFilesToUpload){
       currentFileSize = files.files[i].size;
       currentFile = files.files[i];
       if(currentFileSize < maxUploadSize){
           response =  await makeRequestForUploadingSmallItemAPI(currentFile);
           console.log(response);
       } else {
           let sizeOfDataSent = 0;
           let uploadSessionStarted = 0;
           let cursorId = 0;
           while(currentFileSize - sizeOfDataSent > maxUploadSize){
               if(uploadSessionStarted == 0){
                   fileSliceToSend = currentFile.slice(sizeOfDataSent,sizeOfDataSent + maxUploadSize,currentFile);
                   response = await makeRequestForUploadSessionStartAPI(fileSliceToSend,currentFile.name);
                   uploadSessionStarted = 1;
               } else {
                   fileSliceToSend = currentFile.slice(sizeOfDataSent,sizeOfDataSent + maxUploadSize,currentFile);
                   response = await makeRequestForUploadSessionAppendAPI(fileSliceToSend,currentFile.name);
               }
               sizeOfDataSent = sizeOfDataSent + maxUploadSize;
           }
           if(currentFileSize - sizeOfDataSent < maxUploadSize){
              fileSliceToSend = currentFile.slice(sizeOfDataSent,currentFileSize,currentFile);
              response = await makeRequestForUploadSessionFinishAPI(fileSliceToSend,currentFile.name);
              console.log(response);
           }
       }
       i++;
    }
    return response;
}
function makeRequestForUploadSessionStartAPI(fileSlice,fileName){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        fileArgs = JSON.stringify({ name: fileName });
        xhr.open('POST', 'APIuploadStart', true);
        xhr.setRequestHeader('File-Args',fileArgs);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(fileSlice);
    });
}

function makeRequestForUploadSessionAppendAPI(fileSlice,fileName){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        fileArgs = JSON.stringify({ name: fileName });
        xhr.open('POST', 'APIuploadAppend', true);
        xhr.setRequestHeader('File-Args',fileArgs);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(fileSlice);
    });
}
function makeRequestForUploadSessionFinishAPI(fileSlice,fileName){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        fileArgs = JSON.stringify({ name: fileName });
        xhr.open('POST', 'APIuploadFinish', true);
        xhr.setRequestHeader('File-Args',fileArgs);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(fileSlice);
    });
}

var upload = async function (){
    input = document.getElementById('allCloudMethods');
    uploadFile = window.confirm('Upload a file?');
    if(uploadFile == true ){
        htmlString = '<input type="file" id="uploadFile" multiple size="50" style="display: none;"/>';
        if(document.getElementById('uploadFile') == null){
            input.insertAdjacentHTML('afterend',htmlString);
            document.getElementById('uploadFile').addEventListener("change",async function(){
                files = document.getElementById('uploadFile');
                response = await prepareUpload(files);
                alert(response);
            });
        }
        item = document.getElementById('uploadFile');
    } else { 
        htmlString = '<input type="file" id="uploadFolder" multiple size="50" style="display: none;" webkitdirectory directory/>';
        if(document.getElementById('uploadFolder') == null){
            input.insertAdjacentHTML('afterend',htmlString);
            document.getElementById('uploadFolder').addEventListener("change",async function(){
                folder = document.getElementById('uploadFolder').value;
                alert(folder);
            });
        }
        item = document.getElementById('uploadFolder');
    }
    item.click();
}
var download = async function(){
    alert("Hello");
}
document.getElementById("allCloudMethods").addEventListener('click',upload,false);
document.getElementById("allCloudMethodsDownload").addEventListener('click',download,false);
checkUrl();
