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
function uploadAllDrivers()
{
    let oneDrive = document.getElementById("one-drive");
    let googleDrive = document.getElementById("google-drive");
    let dropBox = document.getElementById("drop-box");
    let allCloudMethods = document.getElementById("allCloudMethods");

    var htmlString = "";
    htmlString = "<input id='fileid' type='file' hidden/>";
    googleDrive.insertAdjacentHTML('beforebegin',htmlString);

    allCloudMethods.addEventListener('click', function(){
        document.getElementById('fileid').click();
    });
    oneDrive.addEventListener('click', function(){
        document.getElementById('fileid').click();
    });
    googleDrive.addEventListener('click', function(){
        document.getElementById('fileid').click();
    });
    dropBox.addEventListener('click', function(){
        document.getElementById('fileid').click();
    });

}
function logOutUser(){
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'logOut', true);
    alert("Your session has ended.");
    xhr.send();
    location.assign('login');
}
