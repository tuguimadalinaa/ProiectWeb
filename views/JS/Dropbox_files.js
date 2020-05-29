console.log('Dropbox');

var updatedFiles = false;

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

async function waitForResponse(reason,folderId) {
     if(reason == 'updateFiles'){
        let result = await makeRequestForFiles(folderId);
        return result;
     } else if(reason == 'changeFolder'){
        let result = await makeRequestForChangingFolder(folderId);
        return result;
     }
}

async function getFolderFiles(folder){
      folderId = folder.getAttribute('id');
      response = await waitForResponse('changeFolder',folderId);
      location.reload();
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
            htmlString = htmlString + ' class="folderIcon" src="../views/IMAGES/file-icon-v1.png" alt="folderIcon" ';
        }
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