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
    document.getElementsByClassName("sideMenu")[0].style.marginLeft = "0";
}
function closeMenu()
{
    document.getElementById("shownMenu").style.width = "0";
    document.getElementsByClassName("sideMenu")[0].style.marginLeft = "0";;
}
function logOutUser(){
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'logOut', true);
    xhr.send();
    //location.assign('login'); 
}
async function checkUrl(){
    var urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('code')!=null){
        if(urlParams.get('scope')!=null)
        {
            let responseJson = await waitForResponse('Token','GoogleDrive'); 
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
        fileArgs = JSON.stringify({ name: file.name, drive: "AllDrives" });
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
        fileArgs = JSON.stringify({ name: fileName, drive: "AllDrives" });
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
                alert("Upload done!");
                //alert(response);
            });
        }
        item = document.getElementById('uploadFile');
        item.click();
    } 
}

function makeRequestForDownload(receivedName,googleDriveId){
    var request = new XMLHttpRequest();
    request.open('POST', 'APIdownloadFile', true);
    fileArgs = JSON.stringify({ name: receivedName, googledrive_id: googleDriveId });
    request.setRequestHeader('Content-Type', 'application/json');
    request.responseType = 'blob';
    request.onload = function() {
      if(request.status === 200) {
        disposition = request.getResponseHeader('Content-Disposition');
        aux = disposition.indexOf("filename");
        filename = disposition.substr(aux + 9);

        // The actual download
        var blob = new Blob([request.response], { type: 'application/octet-stream' });
        var link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    };
    request.send(fileArgs);
}

function makeRequestForGoogleDriveID(receivedName){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'getIdByName?fileName='+receivedName, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}

var download = async function(){
    filename = window.prompt('Enter file name');
    if(filename == null){
        alert('Please enter a not null file name');
        return;
    }else{
        googleDriveId = await makeRequestForGoogleDriveID(filename);
        downloadResponse = await makeRequestForDownload(filename,googleDriveId);
    }
    
    //alert(downloadResponse);
    //Engleza35-36.txt
    //response = await makeRequestForDownload(filename,googleDriveId);
    //alert(response);
    //window.location = 'downloadFolderDropbox?filename=' + filename;
}
document.getElementById("allCloudMethods").addEventListener('click',upload,false);
document.getElementById("allCloudMethodsDownload").addEventListener('click',download,false);
checkUrl();
