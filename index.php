<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <script>
        // 创建一个Socket实例
        var socket =null;//初始是null
        var isLogin=false;//是否登录到服务器上

        function send()
        {
            if(!isLogin)
            {
                alert("请先服务器验证");
                return;
            }

           /* socket.send("msg:"+msg);
           /* //在div 中显示我们的行为
            var p=document.createElement("P");
            p.innerHTML="<span>发送消息</span>"+msg
            document.getElementById("txtcontent").appendChild(p);*/

            var msg=document.getElementById("txtmsg").value;
            var listusers=document.getElementById("listusers");
            var toUserIP=listusers.options[listusers.selectedIndex].value;//发给用户的IP和端口 ，
            var toUserName=listusers.options[listusers.selectedIndex].text;//发给用户的 昵称
            var UserName=document.getElementById("username").value;
            socket.send("chat:<"+toUserIP+">:"+msg+":"+UserName);
            //chat:<192.168.1.10:56002>:xxxxoo>
            //在div 中显示我们的行为
             var p=document.createElement("P");
             p.innerHTML="["+UserName+"]<span>发送消息给["+toUserName+"]</span>"+msg;
             document.getElementById("txtcontent").appendChild(p)

        }
        function connectServer()
        {
            var userName=document.getElementById("username").value;
            if(userName=="")
            {
                alert("用户昵称必填 ");
                return;
            }
            socket=new WebSocket('ws://115.159.118.76:9091/');
            // 打开Socket
            socket.onopen = function(event) {
                socket.send("login:"+userName);
            };
            socket.onmessage = function(event) {

                var getMsg=event.data;
                if(/^notice:success$/.test(getMsg)) //服务器验证通过,后面做任何发送操作
                {
                    isLogin=true;
                }
                else if(/^msg:/.test(getMsg)) //代表是普通消息
                {
                    //<p>xxxxooo</p>
                    
                    var p=document.createElement("P");
                    p.innerHTML="<span>收到消息</span>"+getMsg.replace("msg:","");
                    document.getElementById("txtcontent").appendChild(p);
                }
                else if(/^users:/.test(getMsg))
                {//显示当前已登录用户
                    getMsg=getMsg.replace("users:","");
                    getMsg=eval("("+getMsg+")");
                    document.getElementById("listusers").innerHTML="";
                    for(var key in getMsg)
                    {
                        var option=document.createElement("OPTION");
                        option.value=key;//IP
                        option.innerHTML=getMsg[key];//昵称
                        document.getElementById("listusers").appendChild(option);
                    }
                }
                else
                {

                }
            };
            // 监听Socket的关闭
            socket.onclose = function(event) {
                isLogin=false;

            };
        }
    </script>
</head>
<body>
<div>
     <div id="txtcontent" style="width:500px;height:250px;border: solid 1px gray">

     </div>
    <div>所有用户:<select id="listusers"></select> </div>
    <div>你的昵称:<input type="text" id="username" autocomplete="off"/> </div>
    <div>
        消息内容:
        <textarea   id="txtmsg" cols="60" rows="5" autocomplete="off"></textarea>
    </div>
    <div>
        <button onclick="connectServer()">连接服务器</button>
        <button onclick="send()">发送消息</button>
    </div>
</div>

</body>
</html>