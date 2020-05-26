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

function makeRequestForFiles() {
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'getFolderFilesDropbox?folder_id=root', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}

async function waitForResponse(reason) {
     if(reason == 'updateFiles'){
        let result = await makeRequestForFiles();
        return result;
     }
}

async function checkDropboxFiles(){
   if(updatedFiles == false){
    let responseJson = await waitForResponse('updateFiles');
    let response = JSON.parse(responseJson);
    var folders = document.getElementById('folderContainer');
    var htmlString;
    var currentFolders = Array.from(response);
    for(var i = 0; i < currentFolders.length; i=i+2){
        htmlString = '<div class="folder"> <img id='.concat(currentFolders[i+1]).concat('class="folderIcon" src="../views/IMAGES/folder-icon.png" alt="folderIcon"> <h4>').concat(currentFolders[i]).concat('</h4> </div>');
        folders.insertAdjacentHTML('afterbegin',htmlString);
    }
   } else {
       updatedFiles = true;
   }
}

checkDropboxFiles();