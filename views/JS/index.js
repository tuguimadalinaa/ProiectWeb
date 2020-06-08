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
    //location.assign('login');  //logOut imi merge fara location.assign(), ma redirecteaza la pagina de login din routes.php la ruta /home (Robert)
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
async function makeRequestForUploadSessionStart(fileName)
{
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'uploadLargeStart?fileTransfName='+fileName, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
function makeRequestForUploadSessionAppend(fileSlice,sizeOfDataSent,cursorId,urlToAppend,currentFileSize,last_range){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        sessionArgs = JSON.stringify({ offset: sizeOfDataSent, cursorId: cursorId, url:urlToAppend,totalSize:currentFileSize,lastRange:last_range});
        xhr.open('POST', 'uploadLargeFileAppend', true);
        xhr.setRequestHeader('Session-Args',sessionArgs);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(fileSlice);
    });
}
function makeRequestForUploadSessionFinish(fileSlice,sizeOfDataSent,cursorId,urlToAppend,currentFileSize,last_range){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        sessionArgs = JSON.stringify({ offset: sizeOfDataSent, cursorId: cursorId, url:urlToAppend,totalSize:currentFileSize,lastRange:last_range});
        xhr.open('POST', 'uploadLargeFileFinish', true);
        xhr.setRequestHeader('Session-Args',sessionArgs);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send(fileSlice);
    });
}
/*document.getElementById("fileOneDrive").addEventListener("change", async function(){
    if(this.files && this.files[0])
    {
        let files = this.files;
        let numberOfFilesToUpload = files.length;
        let i = 0;
        let maxUploadSize = 1048576 * 4; // aprox 40 MB
        while(i < numberOfFilesToUpload){
            currentFileSize = files[i].size;
            currentFile = files[i];
            console.log(currentFile.name);
            if(currentFileSize < maxUploadSize){
                response =  await responseForFileTransfer(currentFile,currentFile.name,currentFileSize);
                console.log(response);
       } else {
           let sizeOfDataSent = 0;
           let uploadSessionStarted = 0;
           let cursorId = 0;
           currentFile = files[i];
           response = await makeRequestForUploadSessionStart(currentFile.name);
           console.log(response);
           let urlToUpload = JSON.parse(response);
           urlToUpload = urlToUpload.response;
           let last_range = 0;
           while(currentFileSize - sizeOfDataSent > maxUploadSize){
                    last_range = sizeOfDataSent;
                   fileSliceToSend = currentFile.slice(sizeOfDataSent,sizeOfDataSent + maxUploadSize,currentFile);
                   response = await makeRequestForUploadSessionAppend(fileSliceToSend,sizeOfDataSent + maxUploadSize,cursorId,urlToUpload,currentFileSize,last_range);
                    sizeOfDataSent = sizeOfDataSent + maxUploadSize;
           }
           if((currentFileSize - sizeOfDataSent) < maxUploadSize){
                last_range = sizeOfDataSent;
                fileSliceToSend = currentFile.slice(sizeOfDataSent,currentFileSize,currentFile);
                response = await makeRequestForUploadSessionFinish(fileSliceToSend,sizeOfDataSent,cursorId,urlToUpload,currentFileSize,last_range);
              alert(response);
           }
       }
       i++;
    }
   // return response;
}
});
 document.getElementById('directoryOneDrive').addEventListener("change", function(){
    if (this.files && this.files[0]) {
        for (var i = 0; i < this.files.length; i++) {
            var myFile = this.files[i];
            var reader = new FileReader();
            reader.addEventListener('load',  async function (e) {
                var arrayBuffer = this.result,
                array = new Uint8Array(arrayBuffer),
                binaryString = String.fromCharCode(array);
                let result   = await responseForFileTransfer(binaryString, e.target.fileName);
                console.log(result);
            });
            reader.fileName = myFile.name;
            reader.readAsArrayBuffer(myFile);
        }
      }    
 });*/
 checkUrl();
//https://www.w3schools.com/jsref/tryit.asp?filename=tryjsref_fileupload_files
//https://stackoverflow.com/questions/16210231/how-can-i-upload-a-new-file-on-click-of-image-button
//https://codepen.io/monjer/pen/JKRLzM
/*https://stackoverflow.com/questions/32556664/getting-byte-array-through-input-type-file/32556944 */
/*https://stackoverflow.com/questions/14446447/how-to-read-a-local-text-file */
/*https://stackoverflow.com/questions/32556664/getting-byte-array-through-input-type-file/32556944 */
/*http://jsfiddle.net/SYwuP/*/
/*https://stackoverflow.com/questions/14438187/javascript-filereader-parsing-long-file-in-chunks*/
