console.log('OneDrive');

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
function finished(){
    var elements = document.getElementsByClassName("folder");
    for (var i = 0; i < elements.length; i++) {
        elements[i].addEventListener('dblclick', nextFolder, false);
    }
};
async function getDirectory(name,callback){
    let result   = await responseGetDirectory(name);
    alert(result);
    let response = JSON.parse(result);
    alert(response.value[0].length);
    var folders = document.getElementById('renderFolders');
    for(var i=0;i<response.value.length;i++)
    {
        var keyName = Object.values(response.value[i])[0];
        console.log(keyName);
        console.log(response.value[i]);
        htmlString = "<div class='folder' id=".concat("'").concat(response.value[i].parentReference.path.concat('/').concat(response.value[i].name)).concat("'> <a href='#'><img id=").concat(response.value[i].name).concat('"')
                    .concat(' class="folderIcon" src="../views/IMAGES/folder-icon.png" alt="folderIcon"></a> <h4>')
                    .concat(response.value[i].name).concat('</h4> <button class="folder-button" onclick="getFile('.concat("'").concat(response.value[i].parentReference.path.concat('/').concat(response.value[i].name))
                    .concat("'").concat(')">Hello</button></div>'));
        folders.insertAdjacentHTML('afterbegin',htmlString);
        
        console.log(response.value[i].name);
        console.log(response.value[i].parentReference.path);
        console.log(response.value[i].parentReference.path.concat('/').concat(response.value[i].name));
    }
    callback();
 }

 function makeRequestGetFile(fileName){
    return new Promise(function (resolve) {
       let xhr = new XMLHttpRequest();
       xhr.open('GET', 'getFile?fileTransfName='+fileName, true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function responseGetFile(fileName) {
    let result = await makeRequestGetFile(fileName);
    return result;
}
   
async function getFile(fileName){
    let result   = await responseGetFile(fileName);
    let response = JSON.parse(result);
    if(response.status=='401'){
        alert("Error to load file: " + e.target.fileName);
    }
    else{
        location.assign(response.urlToDownload);
    }
 }
 
 getDirectory('/drive/root:/Documents',finished);