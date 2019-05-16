const electron = require("electron"),
    { app,
        BrowserWindow,
        ipcMain,
        shell
    } = electron
    ,log_url = "./log/log.html";
const fs = require("fs");

/**
 * @var global
 */
let use_opt = {
    width: 384,
    height: 480,
    minWidth: 384,
    minHeight: 480,
    frame: false,   //去掉顶部
    transparent: true,
},windowSize = null;
// app.disableHardwareAcceleration();

function createWindow(opt = {
    width: 384,
    height: 480,
    minWidth: 384,
    minHeight: 480,
    frame: false,   //去掉顶部
    transparent: true,
}){
    return new BrowserWindow(opt);
}

/**
 * @var MainBrowserWindow
 */
let win,
    screen = null;
app.on('ready', () => {
    // {
    //     // alwaysOnTop: true,  //窗口置顶
    //     // useContentSize: true,   //使用web网页size, 这意味着实际窗口的size应该包括窗口框架的size，稍微会大一点，默认
    //       //透明窗口
    //     // type: 'toolbar',    //工具栏窗口
    //     // webPreferences: {
    //     //     devTools: false,    //关闭调试工具
    //     // },
    //     // movable: true,
    //     // maximizable: false,
    // }
    win = createWindow();
    screen = electron.screen;
    win.loadURL('file:/d:/学习/electron/2/app/LoginPage/index.html');
    // win.webContents.openDevTools();

    windowSize = screen.getPrimaryDisplay();
    win.webContents.closeDevTools();
    setTimeout(x=>{
        win.webContents.reload();
    }, 200)
    /*
        Log 日志
    */
    
    fs.exists(log_url,(exists)=>{
        const append_data = "<p>Electron Running Success: " +
              new Date().toLocaleDateString() +
              new Date().toLocaleTimeString() +
              "</p> <p>Author: Mr.Qin </p> <p>Job: webDeveloper </p>"+
              "<p>Running Status: Success</p>" +
              "<div style='height: 30px;'></div>";
        if(exists){
            fs.readFile(log_url, (err, data)=>{
                let old_data = data.toString(),
                    new_data = old_data + "\r\n" + append_data,
                    count = 0;
                count = old_data.split("<div style='height: 30px;'></div>").length;
                if(count > 20){
                    fs.writeFile(log_url, append_data,"utf-8",x=>{})
                }else{
                    fs.writeFile(log_url, new_data,"utf-8",x=>{})
                }
            })
        }else{
            fs.writeFile(log_url, append_data,"utf-8",x=>{});
        }
    })
})

/*
    @Function 获取鼠标坐标轴
    @Author qxt
    @return { x,y }
*/
function SCREENT_POINT(event){
    let response = screen.getCursorScreenPoint();
    event.returnValue = response;
}
ipcMain.on("init-point", event=>{
    let response = screen.getCursorScreenPoint(), // 鼠标点在地方
        _x = response.x,
        _y = response.y;
    let { x,y } = win.getPosition(); //获取现在的x,y
    event.returnValue = {x: _x - x, y: _y - y};
})
ipcMain.on("get-screen-point", event=>{
    SCREENT_POINT(event);
})
/*
    @Lisener 接收新的x,y轴数值并且更改
    @Author qxt
*/
ipcMain.on("change-brower-window-screen", (event, arg)=>{
    let { x,y } = screen.getCursorScreenPoint();
    win.setBounds({width: 384,height: 480, x: x-arg.x,y: y-arg.y});
    event.returnValue = {"msg":"success"};
})

/*
    @Listener 应用部分信息查看
*/
/**
 * @var Log_exe
 */
let exe_log_w = null;
ipcMain.on("open-the-exe-log", e=>{
    exe_log_w = createWindow({
        width: 384,
        height: 480,
        transparent: false,
        frame: false
    });

    exe_log_w.loadFile(log_url);
})

/*
    @Listener 关闭日志查看
*/
ipcMain.on("close-exe-log", event=>{
    let response = {"msg":"success","code": 1};
    if(typeof exe_log_w !== "object"){
        response = {"msg":"error","code":0};
    }else{
        exe_log_w.close();
    }
    event.returnValue = response;
})


/**
*   @Listenr 关闭应用程序
*    @on close-sign
*/
ipcMain.on("close-sign", x=>{
    win.close();

    x.returnValue = 1;
})
/** 
 * @listens 最小化程序
 * @returns { true/false }
*/
ipcMain.on("min-scale", x=>{
    win.minimize();

    x.returnValue = 1;
})

/**
 * @listens 注册账号打开浏览器
 * @return { true/false }
 */
ipcMain.on("register-browser", x=>{
    // shell.openExternal("./html/lo_gin/lo_gin.html");
    shell.openExternal("file://d:/学习/electron/2/html/lo_gin/lo_gin.html");
    x.returnValue = 1;
})

/**
 * @return { null }
 * @method 切换登陆和用户好友显示
 * @author qxt
 * @listens "user-data-storage
 * @var { user_data, user_client }
 */
let user_data = null,
    user_client = null;
ipcMain.on("user-data-storage", (event, arg)=>{
    user_data = arg;
    win.hide();

    setTimeout(x=>win.close(), 200);
    let client_opt = Object.assign({},use_opt),
        { workArea } = windowSize,
        { x, y, width, height } = workArea;
    client_opt.x = 100;
    client_opt.y = 0;
    client_opt.width = 100;
    client_opt.height = height;
    client_opt.movable = true;
    user_client = createWindow(client_opt);
    user_client.loadFile("./app/UserClient/index.html");
    // user_client.webContents.openDevTools();
    console.log(workArea);
    // user_client.addListener("move", function(){
    //     let attr = user_client.getBounds();
    //     const max_x = width - attr.width / 2,
    //           max_y = height - attr.height;
    //     if(attr.x >= max_x){
    //         user_client.setPosition(max_x,0);
    //     }
    //     // user_client.setBounds({width: 384,height ,x: setx, y: sety});
    // })
    event.returnValue = 1;
})
/**
 * @method mouse-up-pos
 * @author qxt
 * @return {array}
 */
ipcMain.on("mouse-up-pos", event=>{
    event.returnValue = user_client.getPosition();
})
/**
 * @return {}
 * @author qxt
 * @method 返回用户数据
 */
ipcMain.on("get-user-data", (event,arg)=>{
    event.sender.send("user-data-response", user_data);
})
/**
 * @function 关闭客户端
 * 
 */
ipcMain.on("close-user-client", x=>{
    user_client.close();
    x.returnValue = 1;
})
/**
 * @function 客户端最小化
 */
ipcMain.on("minimize-user-client", x=>{
    user_client.minimize();
})