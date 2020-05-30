//console.log('Google');

var checkedFiles = false;

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

async function highlightItem(item){
    itemId = item.getAttribute('id');
    clickedItemId = itemId;
    console.log(clickedItemId);
}

async function waitForResponse(reason) {
    if(reason == 'listFiles'){
       let result = await makeRequestForFiles();
       console.log(result);
       //alert(result);
       return result;
    }
}

async function checkGoogleDriveFiles(){
    if(checkedFiles == false){
     let responseJson = await waitForResponse('listFiles');
     alert(responseJson);
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
     checkedFiles = true;
 }

 checkGoogleDriveFiles();