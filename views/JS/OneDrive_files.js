var pressedButton = false;
var selectedFile = '';
var current_directory='';
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
function makeRequestGetDirectory(name){
    return new Promise(function (resolve) {
       let xhr = new XMLHttpRequest();
       xhr.open('GET', 'getDirectoryOneDrive?name='+name, true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function responseGetDirectory(name) {
    let result = await makeRequestGetDirectory(name);
    return result;
}
var nextFolder = function(event) {
    event.preventDefault();
    var x = this.id;
    var element = document.getElementById('renderFolders');
    //let goBackArrow = document.getElementsByClassName("goBackButton");
    element.innerHTML='';
    /*if(goBackArrow[0]!=undefined)
    {
        goBackArrow[0].innerHTML='';
    }*/
    current_directory = x;
    getDirectory(x,finished);
};
var selectFile  = function(event)
{
    event.preventDefault();
    pressedButton=true;
    selectedFile  = this.id;

}
function finished(){
    var elements = document.getElementsByClassName("folder");
    for (var i = 0; i < elements.length; i++) {
        elements[i].addEventListener('dblclick', nextFolder, false);
        elements[i].addEventListener('click',selectFile,false);
    }
    /*let goBackArrow = document.getElementsByClassName("goBackButton");
    if(goBackArrow[0]!=undefined)
    {
        goBackArrow[0].onclick = goBack_;
    }*/
    
};
async function getDirectory(fileName,callback){
    console.log(fileName);
    let result   = await responseGetDirectory(fileName,name);
    let response = JSON.parse(result);
    if(response.status=="Expired Token")
    {
        alert("Your token expired! Please Login again in home page!");
        return;
    }
    let typeOfPhoto='';
    var folders = document.getElementById('renderFolders');
    /*let goBackArrow = document.getElementsByClassName("goBackButton");
    if(goBackArrow[0]!=undefined)
    {
        goBackArrow[0].parentNode.removeChild(goBackArrow[0]);
    }
    if(fileName!="/drive/root" || fileName!="\/drive\/root")
    {
        htmlString='<div class="goBackButton"> <img src="../views/IMAGES/GoBack.png" alt="Go_Back"></div>';
        folders.insertAdjacentHTML('beforebegin',htmlString);
    }*/
    
    for(var i=0;i<response.value.length;i++)
    {
        if(response.value[i].folder!=undefined)
        {
            typeOfPhoto='src="../views/IMAGES/folder-icon-v2.png"';
        }
        else{
            typeOfPhoto='src="../views/IMAGES/file-icon-v1.png"';
        }
        let fileName = response.value[i].name;
        let parentReference = response.value[i].parentReference.path;
        fileName = fileName.split(' ').join('%20');
        parentReference = parentReference.split(' ').join('%20');
        htmlString = "<div class='folder' id=".concat("'").concat(parentReference.concat('/').concat(fileName)).concat("'> <a href='#'><img id=").concat(fileName).concat('"')
                    .concat(' class="folderIcon" ').concat(typeOfPhoto).concat(' alt="folderIcon"></a> <h4>')
                    .concat(response.value[i].name).concat('</h4></div>');
        folders.insertAdjacentHTML('afterbegin',htmlString);
    }
    callback();
 }

 function makeRequestGetFile(fileName,type){
    return new Promise(function (resolve) {
       let xhr = new XMLHttpRequest();
       xhr.open('GET', 'getFile?fileTransfName='+fileName + '&type='+type, true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function responseGetFile(fileName,type) {
    let result = await makeRequestGetFile(fileName,type);
    return result;
}
   
var  getFile = async function()
{
    if(pressedButton==true)
    {
        console.log('In get File ' + selectedFile);
        var div = document.getElementById(selectedFile).childNodes;
        var image =div[1].childNodes;
        var folder = false;
        let result;
        if(image[0].src.includes('folder-icon-v2.png')==true)
        {
            result   = await responseGetFile(selectedFile,'folder'); 
            folder=true;
        }
        else{
            result   = await responseGetFile(selectedFile,'file');
        }
        let response = JSON.parse(result);
        if(response.status=='401'){
            alert("Error to load file: " + selectedFile);
        }
        else{
            if(folder==false)
            {
                location.assign(response.urlToDownload);
            }
            
        }
        pressedButton=false;
    }
    else{
        alert("Please select a file");
    }
 }
 function makeRequestDeleteFile(fileName,type){
    return new Promise(function (resolve) {
       let xhr = new XMLHttpRequest();
       xhr.open('GET', 'deleteFile?fileTransfName='+fileName, true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function responseDeleteFile(fileName) {
    let result = await makeRequestDeleteFile(fileName);
    return result;
}
var  deleteFile = async function()
{
    if(pressedButton==true)
    {
        console.log('In delete File ' + selectedFile);
        var div = document.getElementById(selectedFile).childNodes;
        var image =div[1].childNodes;
        let result = await responseDeleteFile(selectedFile);
        if(result.type!=undefined)
        {
            let response  = JSON.parse(result);
            if(response.status=="Expired Token")
            {
                alert("Your token expired! Please Login again in home page!");
            }
        }
        else{
            alert("Deleted file! Please refresh");
            location.reload();
        }
        pressedButton=false;
    }
    else{
        alert("Please select a file");
    }
 }
 function makeRequestCreateFolder(fileName,path){
    return new Promise(function (resolve) {
       let xhr = new XMLHttpRequest();
       xhr.open('GET', 'createFolder?fileTransfName='+fileName+'&path='+path, true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function responseCreateFolder(fileName,path) {
    let result = await makeRequestCreateFolder(fileName,path);
    return result;
}
 var createFolder =  async function (){

    var folderName = window.prompt('Folder name here');
    if(folderName!=null)
    {
        var elements = document.getElementsByClassName('folder');
        let path  = elements[0].id.split(elements[0].id.match('[\\/][A-za-z]+[\\s]*[\\(]*[0-9]*[\\)]*[\\.]+[a-zA-z]+'));
        let pathWithoutComa  = path[0].split(',');
        let result = await responseCreateFolder(folderName,pathWithoutComa);//pathWithoutCom
        let response = JSON.parse(result);
        if(response.status=='401'){
            alert("Error at creating Folder " + folderName);
        } else if (response.status=="Expired Token")
        {
            alert("Your token expired! Please Login again in home page!");
        }
        else{
            alert("Folder " + folderName+" created")
        }
    }
    
 }
 function makeRequestMoveFolder(fileName,newpath){
    return new Promise(function (resolve) {
       let xhr = new XMLHttpRequest();
       xhr.open('GET', 'moveFile?fileTransfName='+fileName+'&newPath='+newpath, true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function responseMoveFolder(fileName,path) {
    let result = await makeRequestMoveFolder(fileName,path);
    return result;
}
 var moveFolder = async function(){
    
    if(pressedButton==true)
    {
        let folderWhereToMove = window.prompt('Give path to where to move folder');
        console.log('In move File ' + selectedFile);
        folderWhereToMove = '/drive/root:/'+folderWhereToMove;
        console.log(folderWhereToMove);
        let response = await responseMoveFolder(selectedFile,folderWhereToMove);
        //alert(response);
        console.log(response);
        pressedButton=false;
    }
    else{
        alert("Please select a file");
    }
 }
 function makeRequestRenameFolder(fileName,selectedFile){
    return new Promise(function (resolve) {
       let xhr = new XMLHttpRequest();
       xhr.open('GET', 'renameFolder?fileTransfName='+fileName+'&oldName='+selectedFile, true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function responseRename(fileName,selectedFile) {
    let result = await makeRequestRenameFolder(fileName,selectedFile);
    return result;
}
 var renameFolder = async function(){
    if(pressedButton==true)
    {
        let newFolderName = window.prompt('Give new name to Folder');
        console.log(newFolderName);
        let result = await responseRename(newFolderName,selectedFile);
        let response = JSON.parse(result);
        if(response.status=="Expired Token")
        {
            alert("Your token expired! Please Login again in home page!");
        }
        else if(response.status=='401'){
            alert("Error at creating Folder " + newFolderName);
        }
        else{
            alert("Folder " + newFolderName +" renamed")
        }
        pressedButton=false;
    }
    else{
        alert("Please select a file");
    }
 }
 function makeRequestForGoBack(){
    return new Promise(function (resolve) {
       let xhr = new XMLHttpRequest();
       xhr.open('GET', 'goBack', true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function responseGoBack() {
    let result = await makeRequestForGoBack();
    return result;
}
var goBack_ = async function()
 {
    var element = document.getElementById('renderFolders');
    element.innerHTML='';
    let result = await responseGoBack();
    let response = JSON.parse(result);
    console.log(response);
    if(response.status=='/drive/' || response.status=='\/drive\/')
    {
        alert("Can't go back");
        getDirectory("/drive/root",finished);
    }
    else{
        getDirectory(response.status,finished);
    }
    
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
async function responseForFileTransfer(fileData, fileName,fileSize){
    let result = await makeRequestForFileTransfer(fileData,fileName,fileSize);
    return result;
}
 var uploadFile = async function ()
 {
    if(confirm("Upload file?")){
        var buttonAdded = '<input type="file" id="fileOneDrive" multiple size="50" style="display: none;"/>';
        var placeToInsert =  document.getElementsByClassName("containerFlex");
        placeToInsert[0].insertAdjacentHTML('beforebegin',buttonAdded);
        var element = document.getElementById('fileOneDrive');
        if(element.getAttribute('listener')!=='true')
        {
            element.setAttribute('listener','false');
        }
        document.getElementById("fileOneDrive").addEventListener("change", async function()
        {
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
                        
                        response =  await responseForFileTransfer(currentFile,current_directory + '/'+currentFile.name,currentFileSize);
                        let result= JSON.parse(response);
                        if(result.status=="Expired Token")
                        {
                            alert("Your token expired! Please Login again in home page!");
                        }else{
                            alert("File uploaded");
                        }
               } else {
                   let sizeOfDataSent = 0;
                   let uploadSessionStarted = 0;
                   let cursorId = 0;
                   currentFile = files[i];
                   response = await makeRequestForUploadSessionStart(current_directory + '/'+currentFile.name);
                   console.log(response);
                   let urlToUpload = JSON.parse(response);
                   if(urlToUpload.status=="Expired Token")
                   {
                       alert("Your token expired! Please Login again in home page!");
                   }
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
        }});
        element.click();
        element.remove();//?
    }
        
 };
 function makeRequestForDownloadContent(filename){
    return new Promise(function (resolve) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'contentDownload?fileNameTransf=' + filename, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function responseForDownloadcontent(filename){
    let result = await makeRequestForDownloadContent(filename);
    return result;
}
 async function downloadByContent(filename)
 {
    let response =  await responseForDownloadcontent(filename);
    console.log(response);
 }
 document.getElementById('download_button').addEventListener('click',getFile,false);
 document.getElementById('delete_button').addEventListener('click',deleteFile,false);
 document.getElementById('create_folder_button').addEventListener('click',createFolder,false);
 document.getElementById('rename_button').addEventListener('click',renameFolder,false);
 document.getElementById('upload_button').addEventListener('click',uploadFile,false);
 document.getElementById('go_back_button').addEventListener('click',goBack_,false);
 getDirectory("/drive/root",finished);

 //getDirectory("/drive/root:/Documents",finished);
//