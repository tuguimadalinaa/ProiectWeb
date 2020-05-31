//console.log('Google');

var checkedFiles = false;
var checkedFileId=0;

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
function makeRequestForDeletingFile(fileId){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'deleteFileGoogleDrive?folderId='+fileId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function highlightItem(item){
    itemId = item.getAttribute('id');
    checkedFileId = itemId;
    console.log(checkedFileId);
}

async function waitForResponse(reason,fileId) {
    if(reason == 'listFiles'){
       let result = await makeRequestForFiles();
       return result;
    }
    else if(reason=='deleteFile')
    {
        let result=await makeRequestForDeletingFile(fileId);
        return result;
    }
}
async function downloadFileAndFolder(){
    if(checkedFileId == 0){
        alert('Please select a folder or file to download');
    }
    else {
        item = document.getElementById(checkedFileId);
        typeOfItem = item.getAttribute("alt");
         if(typeOfItem == 'fileIcon'){
            window.location = 'downloadFileGoogleDrive?fileId=' + checkedFileId;
        } 
    }
}
async function deleteFile(){
    if(checkedFileId == 0){
        alert('Please select a file/folder to delete first');
    } else {
        response = await waitForResponse('deleteFile',checkedFileId);
        location.reload();
    }
}
async function checkGoogleDriveFiles(){
    if(checkedFiles == false){
     let responseJson = await waitForResponse('listFiles');
     //alert(responseJson);
     let response = JSON.parse(responseJson);
     
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
        // htmlString = htmlString + 'ondblclick="getFolderFiles(this)" >';
         htmlString = htmlString + '<h4>' + currentFolders[i] + '</h4> </div>';
         folder.insertAdjacentHTML('beforeend',htmlString);
    }
     }
     else{
        checkedFiles = true;
     }

 }

 checkGoogleDriveFiles();