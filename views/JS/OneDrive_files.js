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
function makeRequestGetDirectory(){
    return new Promise(function (resolve) {
       let xhr = new XMLHttpRequest();
       xhr.open('GET', 'getDirectoryOneDrive', true);
       xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                resolve(xhr.response);
            }
        };
        xhr.send();
    });
}
async function responseGetDirectory() {
    let result = await makeRequestGetDirectory();
    return result;
}
var myFunction = function() {
    alert("Merge");
};
function finished(){
    alert("Done");
    var elements = document.getElementsByClassName("folder");
    alert(elements.length); 
    for (var i = 0; i < elements.length; i++) {
        elements[i].addEventListener('dblclick', myFunction, false);
    }
};
async function getDirectory(callback){
    let result   = await responseGetDirectory();
    let response = JSON.parse(result);
    var folders = document.getElementById('renderFolders');
    for(var i=0;i<response.value.length;i++)
    {
        var keyName = Object.values(response.value[i])[0];
        console.log(keyName);
        console.log(response.value[i]);
        htmlString = '<div class="folder"> <a href="#"><img id='.concat(response.value[i].name).concat('"')
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
 
 getDirectory(finished);