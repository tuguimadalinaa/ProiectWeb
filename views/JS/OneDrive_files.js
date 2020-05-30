var pressedButton = false;
var selectedFile = '';
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
var nextFolder = function() {
    var x = this.id;
    var element = document.getElementById('renderFolders');
    element.innerHTML='';
    getDirectory(x);
};
var selectFile  = function()
{
    pressedButton=true;
    selectedFile  = this.id;
    console.log(selectFile);

}
function finished(){
    var elements = document.getElementsByClassName("folder");
    for (var i = 0; i < elements.length; i++) {
        elements[i].addEventListener('dblclick', nextFolder, false);
        elements[i].addEventListener('click',selectFile,false);
    }
};
async function getDirectory(name,callback){
    let result   = await responseGetDirectory(name);
    let response = JSON.parse(result);
    let typeOfPhoto='';
    var folders = document.getElementById('renderFolders');
    for(var i=0;i<response.value.length;i++)
    {
        console.log(response.value[i]);
        var keyName = Object.values(response.value[i])[0];
        if(response.value[i].folder!=undefined)
        {
            typeOfPhoto='src="../views/IMAGES/folder-icon-v2.png"';
        }
        else{
            typeOfPhoto='src="../views/IMAGES/file-icon-v1.png"';
        }
        htmlString = "<div class='folder' id=".concat("'").concat(response.value[i].parentReference.path.concat('/').concat(response.value[i].name)).concat("'> <a href='#'><img id=").concat(response.value[i].name).concat('"')
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
        console.log(image[0].src);
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
        console.log(result);
        pressedButton=false;
    }
    else{
        alert("Please select a file");
    }
 }
 document.getElementById('download_button').addEventListener('click',getFile,false);
 document.getElementById('delete_button').addEventListener('click',deleteFile,false);
 getDirectory('/drive/root:/Documents',finished);