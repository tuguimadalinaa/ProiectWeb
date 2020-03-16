function registerUser()
{
    let userName = document.getElementById("usernameInput").value;
    let password = document.getElementById("passwordInput").value;
    let passwordCheck = document.getElementById("passwordCheck").value;
    
    var objPeople =[
        {
            username:"a@gmail.com",
        },

        {
            username:"b@gmail.com",
        },
        {
            username:"c@yahoo.com",

        }
    ]

    if(password.length<5)
    {
        alert("You must enter a password with minimum of 5 characters.");
        return true;
    }
    else if(passwordCheck=="")
    {
        alert("You must enter the password again!");
        return true;
    }
   else if(password!=passwordCheck)
   {
    alert("Both password don't match!");
    return true ;
   }
   for(i=0;i<objPeople.length;i++)
   {
       if(userName==objPeople[i].username)
       {
           alert("This username exists! Please enter another username!");
           return true;
       }
   }
        let pathName=window.location.pathname; 
        let directory = pathName.substring(0, pathName.lastIndexOf('/'));
        let newPage  = directory + "/registrationConfirmed.html";
        window.location.href= newPage;
        alert("You sign-uped well. You will be redirected to registration page.");
        return false;
}
