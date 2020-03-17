function goToIndex()
{
    let userName = document.getElementById("usernameInput").value;
    let password = document.getElementById("passwordInput").value;
    var result = JSON.parse(login);
    alert(result);

    var objPeople=[
        {
            username:"a@gmail.com",
            password:"blablabla"
        },
        {
            username:"b@gmail.com",
            password:"tucu"
        },
        {
            username:"c@gmail.com",
            password:"ex"
        }
    ]
    for(i=0;i<objPeople.length;i++)
    {
        if(userName==objPeople[i].username && password==objPeople[i].password)
        {
            let pathName=window.location.pathname;
            let directory = pathName.substring(0, pathName.lastIndexOf('/'));
            let newPage  = directory + "/index.html";
            window.location.href= newPage;
            alert("You logged well. You will be redirected to the next page.");
            return false;
        }
        
    }
    alert("Wrong username or password!");
    return true;
}
function goToSignUp()
{
    let pathName=window.location.pathname; 
    let directory = pathName.substring(0, pathName.lastIndexOf('/'));
    let newPage  = directory + "/signUp.html";
    window.location.href= newPage;
}
