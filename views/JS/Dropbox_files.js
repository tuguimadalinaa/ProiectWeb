
var updatedFiles = false;
var clickedItemId = 0;

function openMenu() {
    document.getElementById("shownMenu").removeAttribute("hidden");
    document.getElementById("shownMenu").style.width = "250px";
    document.getElementById("sideMenu").style.marginLeft = "250px";
}
function closeMenu() {
    document.getElementById("shownMenu").style.width = "0";
    document.getElementById("sideMenu").style.marginLeft= "0";
}

function makeRequestForFiles(folderId) {
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'getFolderFilesDropbox', true);
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
        xhr.open('GET', 'changeFolderDropbox?folder_id='+folderId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}

function makeRequestForDeletingItem(itemId){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'deleteItemDropbox?item_id='+itemId, true);
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
        xhr.open('GET', 'previousFolderDropbox', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}

function makeRequestForRenamingItem(itemId,newName){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'renameItemDropbox?item_id='+itemId+'&new_name='+newName, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}

function makeRequestForCreatingFolder(folderName){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'createFolderDropbox?folder_name='+folderName, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}

function makeRequestForMovingItem(itemId){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'moveItemDropbox?item_id='+itemId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}

function makeRequestForUploadingSmallItem(file){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        fileArgs = JSON.stringify({ path: file.name });
        xhr.open('POST', 'uploadSmallFileDropbox', true);
        xhr.setRequestHeader('File-Args',fileArgs);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(file);
    });
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

function makeRequestForUploadSessionStart(fileSlice){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'uploadLargeFileStartDropbox', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(fileSlice);
    });
}

function makeRequestForUploadSessionAppend(fileSlice,sizeOfDataSent,cursorId){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        sessionArgs = JSON.stringify({ offset: sizeOfDataSent, cursorId: cursorId });
        xhr.open('POST', 'uploadLargeFileAppendDropbox', true);
        xhr.setRequestHeader('Session-Args',sessionArgs);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(fileSlice);
    });
}

function makeRequestForUploadSessionFinish(fileSlice,sizeOfDataSent,cursorId,fileName){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        sessionArgs = JSON.stringify({ offset: sizeOfDataSent, cursorId: cursorId, name: fileName });
        xhr.open('POST', 'uploadLargeFileFinishDropbox', true);
        xhr.setRequestHeader('Session-Args',sessionArgs);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(fileSlice);
    });
}

async function waitForResponse(reason,itemId,extraParameter) {
     if(reason == 'updateFiles'){
        let result = await makeRequestForFiles();
        return result;
     } else if(reason == 'changeFolder'){
        let result = await makeRequestForChangingFolder(itemId);
        return result;
     } else if(reason == 'deleteItem'){
        let result = await makeRequestForDeletingItem(itemId);
        return result;
     } else if(reason == 'goBackToPreviousFolder'){
        let result = makeRequestForGoingToPreviousFolder();
        return result;
     } else if(reason == 'renameItem'){
        let result = makeRequestForRenamingItem(itemId,extraParameter);
        return result;
     } else if(reason == 'createFolder'){
        let result = makeRequestForCreatingFolder(extraParameter);
        return result;
     } else if(reason == 'moveItem'){
        let result = makeRequestForMovingItem(itemId);
        return result;
     }
}

async function getFolderFiles(folder){
    folderId = folder.getAttribute('id');
    response = await waitForResponse('changeFolder',folderId,null);
    location.reload();
}

async function highlightItem(item){
    itemId = item.getAttribute('id');
    clickedItemId = itemId;
    console.log(clickedItemId);
}

async function deleteItem(){
    if(clickedItemId == 0){
        alert('Please select a file/folder to delete');
    } else {
        response = await waitForResponse('deleteItem',clickedItemId,null);
        alert(response);
        location.reload();
    }
}

async function prepareUpload(files){
    let numberOfFilesToUpload = files.files.length;
    let i = 0;
    let maxUploadSize = 1048576 * 40; // aprox 40 MB
    while(i < numberOfFilesToUpload){
       currentFileSize = files.files[i].size;
       currentFile = files.files[i];
       if(currentFileSize < maxUploadSize){
           //response =  await makeRequestForUploadingSmallItem(currentFile);
           response =  await makeRequestForUploadingSmallItemAPI(currentFile);
           console.log(response);
       } else {
           let sizeOfDataSent = 0;
           let uploadSessionStarted = 0;
           let cursorId = 0;
           while(currentFileSize - sizeOfDataSent > maxUploadSize){
               if(uploadSessionStarted == 0){
                   fileSliceToSend = currentFile.slice(sizeOfDataSent,sizeOfDataSent + maxUploadSize,currentFile);
                   //response = await makeRequestForUploadSessionStart(fileSliceToSend);
                   response = await makeRequestForUploadSessionStartAPI(fileSliceToSend,currentFile.name);
                   //cursorId = response;
                   //alert(cursorId);
                   uploadSessionStarted = 1;
               } else {
                   fileSliceToSend = currentFile.slice(sizeOfDataSent,sizeOfDataSent + maxUploadSize,currentFile);
                   //response = await makeRequestForUploadSessionAppend(fileSliceToSend,sizeOfDataSent,cursorId);
                   response = await makeRequestForUploadSessionAppendAPI(fileSliceToSend,currentFile.name);
                   //alert(response);
               }
               sizeOfDataSent = sizeOfDataSent + maxUploadSize;
           }
           if(currentFileSize - sizeOfDataSent < maxUploadSize){
              fileSliceToSend = currentFile.slice(sizeOfDataSent,currentFileSize,currentFile);
              //response = await makeRequestForUploadSessionFinish(fileSliceToSend,sizeOfDataSent,cursorId,currentFile.name);
              response = await makeRequestForUploadSessionFinishAPI(fileSliceToSend,currentFile.name);
              console.log(response);
           }
       }
       i++;
    }
    return response;
}

async function upload(){
    input = document.getElementById('upload_button');
    uploadFile = window.confirm('Upload a file?');
    if(uploadFile == true ){
        htmlString = '<input type="file" id="uploadFile" multiple size="50" style="display: none;"/>';
        if(document.getElementById('uploadFile') == null){
            input.insertAdjacentHTML('afterend',htmlString);
            document.getElementById('uploadFile').addEventListener("change",async function(){
                files = document.getElementById('uploadFile');
                response = await prepareUpload(files);
                //location.reload();
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


async function moveItem(){
    response = await waitForResponse('moveItem',clickedItemId,null);
    if(response == 'Item stored in cookie'){

    } else if(response == 'Not a valid item id'){
        alert('Please choose a file/folder to move!');
    } else {
        location.reload();
    }
}

async function renameItem(){
   if(clickedItemId == 0){
       alert('Please select a file/folder to rename');
   } else {
       while(true){
           newName = window.prompt('Enter new name');
           if(newName == null){
               alert('Please enter a not null new name');
           } else {
               break;
           }
       }
       response = await waitForResponse('renameItem',clickedItemId,newName);
       location.reload();
   }
}

async function createFolder(){
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

async function goBackToPreviousFolder(){
    response = await waitForResponse('goBackToPreviousFolder',null,null);
    location.reload();
}

async function downloadItem(){
    if(clickedItemId != 0){
        item = document.getElementById(clickedItemId);
        typeOfItem = item.getAttribute('alt');
        if(typeOfItem == 'folderIcon'){
            window.location = 'downloadFolderDropbox?folder_id=' + clickedItemId;
        } else if(typeOfItem == 'fileIcon'){
            window.location = 'downloadFileDropbox?file_id=' + clickedItemId;
        } else {
            
        }
    } else{
        alert('Please select a folder or file to download');
    }
}

async function checkDropboxFiles(){
   if(updatedFiles == false){
    let responseJson = await waitForResponse('updateFiles',null,null);
    let response = JSON.parse(responseJson);
    var folder = document.getElementById('folderContainer');
    var htmlString;
    var currentFolders = Array.from(response);
    for(var i = 0; i < currentFolders.length; i=i+2){
        htmlString = '<div class="folder"> <img id="' + currentFolders[i+1] + '"';
        if(currentFolders[i].includes('.') == false){
            htmlString = htmlString + ' class="folderIcon" src="../views/IMAGES/folder-icon-v2.png" alt="folderIcon" ';
        } else {
            htmlString = htmlString + ' class="folderIcon" src="../views/IMAGES/file-icon-v1.png" alt="fileIcon" ';
        }
        htmlString = htmlString + 'onclick="highlightItem(this)" ';
        htmlString = htmlString + 'ondblclick="getFolderFiles(this)" >';
        htmlString = htmlString + '<h4>' + currentFolders[i] + '</h4> </div>';
        folder.insertAdjacentHTML('beforeend',htmlString);
    }
   } else {
       updatedFiles = true;
   }
}

checkDropboxFiles();