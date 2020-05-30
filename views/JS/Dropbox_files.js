console.log('Dropbox');

var updatedFiles = false;
var clickedItemId = 0;

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

function makeRequestForFiles(folderId) {
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'getFolderFilesDropbox?folder_id='+folderId, true);
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

async function waitForResponse(reason,itemId) {
     if(reason == 'updateFiles'){
        let result = await makeRequestForFiles(itemId);
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
     }
}

async function getFolderFiles(folder){
      folderId = folder.getAttribute('id');
      response = await waitForResponse('changeFolder',folderId);
      location.reload();
}

async function highlightItem(item){
    itemId = item.getAttribute('id');
    clickedItemId = itemId;
    console.log(clickedItemId);
}

async function deleteItem(){
    if(clickedItemId == 0){
        alert('Please select a file/folder to delete first');
    } else {
        response = await waitForResponse('deleteItem',clickedItemId);
        location.reload();
    }
}

async function goBackToPreviousFolder(){
    response = await waitForResponse('goBackToPreviousFolder',null);
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
    dropboxCookieValue = getDropboxCookie('Dropbox');
    let responseJson = await waitForResponse('updateFiles',dropboxCookieValue);
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

/*https://www.w3schools.com/js/js_cookies.asp */
function getDropboxCookie(cookieName) {
    var name = cookieName + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }

checkDropboxFiles();