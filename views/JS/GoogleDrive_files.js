//console.log('Google');

var checkedFiles = false;
var checkedFileId=0;

function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
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

function makeRequestForFiles() {
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'listGoogleDrive', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                //console.log(xhr.response);
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
function makeRequestForMetaDataFile(fileId)
{
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'getMetadataFileGoogleDrive?fileId='+fileId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
function makeRequestForChangingFolder(folderId){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'changeFolderGoogleDrive?fileId='+folderId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
function makeRequestCreateFolder(fileName){
    return new Promise(function (resolve) {
       let xhr = new XMLHttpRequest();
       xhr.open('POST', 'createFolderGoogleDrive?fileName='+fileName, true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}

function makeRequestForDeletingFile(fileId){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'deleteFileGoogleDrive?fileId='+fileId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
function makeRequestForRenamingFile(fileName,fileId){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('PATCH', 'renameFileGoogleDrive?fileName='+fileName+'&fileId='+fileId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
function makeRequestForGoingToPreviousFolder(){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'previousFolderGoogleDrive', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
function makeRequestForMovingFile(fileId){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('PATCH', 'moveFileGoogleDrive?fileId='+fileId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}

function makeRequestForUploadUriFile(file)
{
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'obtainUriForUploadGoogleDrive?fileName='+ file.name, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
function makeRequestForUploadSmallFile(link,file)
{
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        url = JSON.stringify({ linkusor: link});
        xhr.open('POST', 'uploadSmallFilesGoogleDrive', true);
        xhr.setRequestHeader('File-Link',url);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(file);
    });
}

function makeRequestForUploadLargeFile(link,file,start,end,sizeFile)
{
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        url = JSON.stringify({ linkusor: link , Start: start, End: end, SizeFile: sizeFile});
        xhr.open('POST', 'uploadLargeFilesGoogleDrive', true);
        xhr.setRequestHeader('File-Link',url);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(file);
    });
}
function makeRequestForUploadSmallFileAPI(file){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        fileArgs = JSON.stringify({ name: file.name , drive:"GoogleDrive"});
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
function makeRequestForUploadLargeFileAPI(file,fileName){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        fileArgs = JSON.stringify({ name: fileName , drive: "GoogleDrive"});
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
        fileArgs = JSON.stringify({ name: fileName});
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
        fileArgs = JSON.stringify({ name: fileName});
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
function makeRequestForGettingSizeFile(fileId)
{
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'getFileSizeGoogleDrive?fileId='+fileId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
function makeRequestForDownloadSmallFile(fileId){
    var request = new XMLHttpRequest();
    request.open('GET', 'downloadSmallFileGoogleDrive?fileId=' +fileId, true);
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
    request.send();
}
function makeRequestForDownloadLargeFile(fileId){
    var request = new XMLHttpRequest();
    request.open('GET', 'downloadLargeFileGoogleDrive?fileId=' +fileId, true);
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
    request.send();
}
async function highlightItem(item){
    itemId = item.getAttribute('id');
    checkedFileId = itemId;
    console.log(checkedFileId);
}
async function getFolderFiles(folder){
    folderId = folder.getAttribute('id');
    alert(folderId);
    response = await waitForResponse('visualizeFolder',folderId,null);
    location.reload();
}

async function waitForResponse(reason,fileId,fileName) {
    if(reason == 'listFiles'){
       let result = await makeRequestForFiles();
       return result;
    }else if(reason == 'visualizeFolder'){
        let result = await makeRequestForChangingFolder(fileId);
        return result;
    }
    else if(reason == 'goBackToPreviousFolder'){
        let result = makeRequestForGoingToPreviousFolder();
        return result;
     }
    else if(reason=='deleteFile')
    {
        let result=await makeRequestForDeletingFile(fileId);
        return result;
    }
    else if(reason=='createFolder')
    {
        let result=await makeRequestCreateFolder(fileName);
        return result;
    }
    else if(reason=='renameFile')
    {
        let result=await makeRequestForRenamingFile(fileName,fileId);
        return result;
    }
    else if(reason=='moveFile')
    {
        let result=await makeRequestForMovingFile(fileId);
        return result;
    }
}
async function startUpload(files){
    let numberOfFilesToUpload = files.files.length;
    let i = 0;
    let maxUploadSize = 256 * 1024 * 128; // aprox 32 MB
    let ok=0;
    while(i < numberOfFilesToUpload){
       let currentFileSize = files.files[i].size;
       let currentFile = files.files[i];
       let sizeOfDataSent = 0;
       if(currentFileSize < maxUploadSize){
        response =  await makeRequestForUploadSmallFileAPI(currentFile);
        console.log(response);
        location.reload();
    }
    else{
        while(currentFileSize - sizeOfDataSent > maxUploadSize){
            if(ok==0)
            {
                fileSliceToSend = currentFile.slice(sizeOfDataSent,sizeOfDataSent + maxUploadSize,currentFile);
                response=await makeRequestForUploadSessionStartAPI(fileSliceToSend,currentFile.name);
                sizeOfDataSent = sizeOfDataSent + maxUploadSize;
                ok=1;
            }
            else{
                fileSliceToSend = currentFile.slice(sizeOfDataSent,sizeOfDataSent + maxUploadSize,currentFile);
                response=await makeRequestForUploadSessionAppendAPI(fileSliceToSend,currentFile.name);
                sizeOfDataSent = sizeOfDataSent + maxUploadSize;
            }
        }
        if(currentFileSize - sizeOfDataSent < maxUploadSize)
        {
            fileSliceToSend = currentFile.slice(sizeOfDataSent,sizeOfDataSent + maxUploadSize,currentFile);
            response=await makeRequestForUploadLargeFileAPI(fileSliceToSend,currentFile.name);
            sizeOfDataSent = sizeOfDataSent + maxUploadSize;
        }
    }
       i++;
    }
    alert("Upload Done");
    location.reload();
}
async function startDownload(fileId){
    let maxDownloadSize = 256 * 1024 * 128;//32 mb
    let fileSize = await makeRequestForGettingSizeFile(fileId);
    let sizeOfDataSent = 0;
    if(fileSize-sizeOfDataSent<maxDownloadSize)
    {
        response=makeRequestForDownloadSmallFile(fileId);
    }
    else
    {
        response=makeRequestForDownloadLargeFile(fileId);
    }
    //return "Done";
}
async function downloadFileAndFolder(){
    if(checkedFileId == 0){
        alert('Please select a  file to download');
    }
    else {
        item = document.getElementById(checkedFileId);
        typeOfItem = item.getAttribute("alt");
         if(typeOfItem == 'fileIcon'){
            response=startDownload(checkedFileId);
        } 
        else{
            response=startDownload(checkedFileId);

        }
    }
    //return "Download done";
}

async function moveFileOrFolder(){
    response = await waitForResponse('moveFile',checkedFileId,null);
    if(response == 'File stored in cookie'){

    } else if(response == 'Not a valid file id'){
        alert('Please choose a file/folder to move!');
    } else {
        location.reload();
    }
 }

 async function renameFileOrFolder(){
    if(checkedFileId == 0){
        alert('Please select a file/folder to move');
    } else {
        while(true){
            fileName = window.prompt('Enter new name');
            if(fileName == null){
                alert('Please enter a not null new name');
            } else {
                break;
            }
        }
        response = await waitForResponse('renameFile',checkedFileId,fileName);
        alert(response);
        alert('File updated successfully')
        location.reload();
        
    }
 }
async function goBackToPreviousFolder(){
    response = await waitForResponse('goBackToPreviousFolder',null,null);
    location.reload();
}
async function deleteFile(){
    if(checkedFileId == 0){
        alert('Please select a file/folder to delete first');
    } else {
        response = await waitForResponse('deleteFile',checkedFileId);
        alert("Your file was deleted successfully.");
        location.reload();
    }
}
async function createFolder()
{
   while(true){
        folderName = window.prompt('Enter the name for the folder');
        if(folderName == null){
            alert('Please enter a not null name');
        } else {
            break;
        }
    }
    response = await waitForResponse('createFolder',null,folderName);
    location.reload();
}
async function uploadFiles(){
    input = document.getElementById('upload_button');
    uploadFile = window.confirm('Upload a file?');
    if(uploadFile == true ){
        htmlString = '<input type="file" id="uploadFile" multiple size="50" style="display: none;"/>';
        if(document.getElementById('uploadFile') == null){
            input.insertAdjacentHTML('afterend',htmlString);
            document.getElementById('uploadFile').addEventListener("change",async function(){
                files = document.getElementById('uploadFile');
                response = await startUpload(files);

            });
        }
        item = document.getElementById('uploadFile');
        item.click();
    } 
}
async function checkGoogleDriveFiles(){
    if(checkedFiles == false){
     let responseJson = await waitForResponse('listFiles');
     var response;
     if(IsJsonString(responseJson)==true)
     {
        response = JSON.parse(responseJson);
    
     }
     else
     {
        alert("Ups, your token has expired or doesn't exist for this service. Go to home page and press GoogleDrive icon to get another!");
     }
     var folder = document.getElementById('folderContainer');
     var htmlString;
     var currentFolders = Array.from(response);
     for(var i = 0; i < currentFolders.length; i=i+2){
        htmlString = '<div class="folder"> <img id="' + currentFolders[i+1] + '"';
         if(currentFolders[i].includes('.')==false)
         {
            htmlString = htmlString + ' class="folderIcon" src="../views/IMAGES/folder-icon-v2.png" alt="folderIcon" ';
         }
        else
        {
            htmlString = htmlString + ' class="folderIcon" src="../views/IMAGES/file-icon-v1.png" alt="fileIcon" ';
        }
         htmlString = htmlString + 'onclick="highlightItem(this)" ';
         htmlString = htmlString + 'ondblclick="getFolderFiles(this)" >';
         htmlString = htmlString + '<h4>' + currentFolders[i] + '</h4> </div>';
         folder.insertAdjacentHTML('beforeend',htmlString);
    }
     }
     else{
        checkedFiles = true;
     }

 }
 
 checkGoogleDriveFiles();