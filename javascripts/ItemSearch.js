function ShowItems(SearchOrSelect, Category, Code, Description, MaxItems, identifier)
{
if (Category=="")
  {
  document.getElementById("txtHint").innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
    }
  }
  if (SearchOrSelect=='Search') {
		xmlhttp.open("GET","includes/ItemShowSearch.php?Category="+Category+"&Code="+Code+"&Description="+Description+"&MaxItems="+MaxItems+"&identifier="+identifier,true);
	} else {
		xmlhttp.open("GET","includes/ItemShowSelect.php?Category="+Category+"&Code="+Code+"&Description="+Description+"&MaxItems="+MaxItems+"&identifier="+identifier,true);
	}
xmlhttp.send();
}