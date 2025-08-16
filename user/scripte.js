var z,tst,set=0;
var subId=null, activate=0;act=50;
	var NS = new Boolean();
	if(navigator.appName == "Netscape")
		NS = true;
	else
		NS = false;
		
	
	function codiere()
	{
		var str = document.login.user.value+"*"+document.login.xx.value;
		document.login.xx.value = MD5(str);
		document.login.submit();
	}
	
	function checkInput()
	{
		if(document.register.user.value=="" || document.register.xx.value=="")
		{
			alert("bitte geben Sie Ihre E-Mail adresse und ein Passwort!")
		}
		else
		{
			codiere();document.register.submit();
		}
	}
	
	function checkScript()
	{
		if(document.scriptform.title.value!="")
		{
			if(document.scriptform.text.value!="")
			{
				if(document.scriptform.srcfile.value!="")
				{
					document.scriptform.submit();
					return;
				}
			}
		}
		alert("Bitte füllen Sie alle Pflichtfelder aus");
	}
	
	function help()
	{
		alert("In dem Archiv sollten Sourcecode und evtl. ein SQL-Dump mit den dazugehörigen Tabellen enthalten sein.");
	}
	var oldback,oldtext;
	var stat=0;
	var i=0;
	var acty=0;
	var actid;
	function moveElementUp(id)
	{
		if(stat!=1)
		{			
			if(id == null)
				id=actid;
			else
			{
				i=0;
				if(actid!=null)
				{
					var elem = document.getElementById(actid);
					elem.style.top = 40;
				}
				actx=event.x-2;
				actid=id;
			}
			var elem = document.getElementById(id);
			elem.style.top = 40 - i;
			elem.style.left=actx;
			i=i+2;
			if(i<=27)
				setTimeout("moveElementUp()", 10);
			else
			{
				i=28;
				//stat=1;
			}
		}
	}
	
	function moveElementDown(id)
	{
		stat=1;
		if(stat!=0)
		{
			if(id == null)
				id=actid;
			else
				actid=id;
			var elem = document.getElementById(id);
			if(elem==null)
				return;
			elem.style.top = 40-i;
			i=i-2;
			if(i>=0)
				setTimeout("moveElementDown()", 10);
			else
			{
				i=0;
				stat=0;
			}
		}
			
	}
	
	function highLight(id)
	{
		var elem = document.getElementById(id);
		oldback = elem.style.backgroundColor;
		oldtext = elem.style.color
		elem.style.backgroundColor="#0000ff";
		elem.style.color="#ffffff";
	}
	
	function lowLight(id)
	{
		var elem = document.getElementById(id);
		elem.style.backgroundColor=oldback;
		elem.style.color=oldtext;
	}
	function gotoURL(url)
	{
		moveElementDown();
		parent.main.location.href=url;
	}
	function moveElementUpStat(id)
	{
		if(id!=null)
		actid=id;	
		var elem = document.getElementById(actid);
		if(elem == null)
			return;
		elem.style.left = -2048 + i;
		i=i+64;
		if(i<=2048)
			setTimeout("moveElementUpStat()", 10);
		else
		{
			i=0;
			setPos();
		}
	}
	function setPos()
	{
		if(actid==null)
			actid=1;
		else
			actid++;
		moveElementUpStat(actid);
	}