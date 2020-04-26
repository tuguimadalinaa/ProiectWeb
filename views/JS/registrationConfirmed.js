function goToLogin(){
    let pathName=window.location.pathname; 
    let directory = pathName.substring(0, pathName.lastIndexOf('/'));
    let newPage  = directory + "/login.html";
    window.location.href= newPage;
}