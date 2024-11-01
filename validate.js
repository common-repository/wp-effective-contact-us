
function validateForm()
{
var contact_name = document.forms["myform"]["contact_name"].value;
var mail = document.forms["myform"]["email"].value;
var phoneno =document.forms["myform"]["phoneno"].value;
var messagesubject = document.forms["myform"]["messagesubject"].value;
var messagedata = document.forms["myform"]["messagedata"].value;

if (contact_name == null || contact_name == "")
  {
  alert(" Please Enter Your Name");
  return false;
  }

  if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail))
{
//return true;
}
else if(mail!="")
{
alert("Invalid E-mail Address! Please re-enter!");
return false
}
else
{
alert("email is empty");
return false;
}

if (phoneno == null || phoneno == "")
  {
  alert(" PhoneNo is not Entered");
  return false;
  }

if(isNaN(phoneno)||phoneno.indexOf(" ")!=-1)
   {
        alert("Enter numeric value in phoneno field")
        return false;
   }

if (messagesubject==null || messagesubject=="")
  {
  alert(" messagesubject is not Entered");
  return false;
  }

if (messagedata==null || messagedata=="")
  {
  alert(" messagedata is not Entered");
  return false;
  }
  if(document.getElementById("security_code").value=="")
   {
       alert("Please enter captcha");
       return false;
   }
}
