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
    alert("pressed button");
}
function triggerLoadOnGoogleDrive()
{
    var link = document.getElementById("uploadGoogleDrive");
    link.click();
}
function triggerLoadOnDropBox()
{
    var link = document.getElementById("uploadDropBox");
    link.click();
}
function triggerLoadOnOneDrive()
{
    var link = document.getElementById("uploadOneDrive");
    link.click();
}
