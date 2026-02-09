const { app, BrowserWindow, Menu, shell, dialog } = require('electron');
const path = require('path');
const PhpServerManager = require('./php-server');
const IpcHandlers = require('./ipc-handlers');

// Keep a global reference of the window object
let mainWindow;
let phpServerManager;
let ipcHandlers;

// Enable live reload for development
if (process.env.NODE_ENV === 'development') {
    require('electron-reload')(__dirname, {
        electron: path.join(__dirname, '..', 'node_modules', '.bin', 'electron'),
        hardResetMethod: 'exit'
    });
}

function createWindow() {
    // Create the browser window
    mainWindow = new BrowserWindow({
        width: 1400,
        height: 900,
        minWidth: 1200,
        minHeight: 800,
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true,
            enableRemoteModule: false,
            preload: path.join(__dirname, 'preload.js')
        },
        icon: path.join(__dirname, 'assets', 'icon.png'),
        show: false,
        titleBarStyle: process.platform === 'darwin' ? 'hiddenInset' : 'default'
    });

    // Show window when ready to prevent visual flash
    mainWindow.once('ready-to-show', () => {
        mainWindow.show();
        
        // Focus on window
        if (process.platform === 'darwin') {
            app.dock.show();
        }
    });

    // Load the app
    if (process.env.NODE_ENV === 'development') {
        mainWindow.loadURL('http://localhost:5173');
        mainWindow.webContents.openDevTools();
    } else {
        // In production, load from the PHP server
        const serverStatus = phpServerManager.getStatus();
        setTimeout(() => {
            mainWindow.loadURL(serverStatus.url);
        }, 3000); // Give PHP server time to start
    }

    // Handle external links
    mainWindow.webContents.setWindowOpenHandler(({ url }) => {
        shell.openExternal(url);
        return { action: 'deny' };
    });

    // Emitted when the window is closed
    mainWindow.on('closed', () => {
        mainWindow = null;
    });

    // Handle window controls for Windows/Linux
    if (process.platform !== 'darwin') {
        mainWindow.on('minimize', () => {
            mainWindow.hide();
        });
    }
}

// PHP server functions are now handled by PhpServerManager class

function createMenu() {
    const template = [
        {
            label: 'ConCure',
            submenu: [
                {
                    label: 'About ConCure',
                    click: () => {
                        dialog.showMessageBox(mainWindow, {
                            type: 'info',
                            title: 'About ConCure',
                            message: 'ConCure Clinic Management System',
                            detail: 'Version 1.0.0\nDeveloped by Connect Pure\n\nA comprehensive clinic management solution for healthcare providers.'
                        });
                    }
                },
                { type: 'separator' },
                {
                    label: 'Preferences',
                    accelerator: 'CmdOrCtrl+,',
                    click: () => {
                        // Open preferences in the app
                        mainWindow.webContents.executeJavaScript(`
                            if (window.location.pathname !== '/settings') {
                                window.location.href = '/settings';
                            }
                        `);
                    }
                },
                { type: 'separator' },
                {
                    label: 'Quit',
                    accelerator: process.platform === 'darwin' ? 'Cmd+Q' : 'Ctrl+Q',
                    click: () => {
                        app.quit();
                    }
                }
            ]
        },
        {
            label: 'Edit',
            submenu: [
                { role: 'undo' },
                { role: 'redo' },
                { type: 'separator' },
                { role: 'cut' },
                { role: 'copy' },
                { role: 'paste' },
                { role: 'selectall' }
            ]
        },
        {
            label: 'View',
            submenu: [
                { role: 'reload' },
                { role: 'forceReload' },
                { role: 'toggleDevTools' },
                { type: 'separator' },
                { role: 'resetZoom' },
                { role: 'zoomIn' },
                { role: 'zoomOut' },
                { type: 'separator' },
                { role: 'togglefullscreen' }
            ]
        },
        {
            label: 'Window',
            submenu: [
                { role: 'minimize' },
                { role: 'close' }
            ]
        },
        {
            label: 'Help',
            submenu: [
                {
                    label: 'Documentation',
                    click: () => {
                        shell.openExternal('https://github.com/your-repo/concure-clinic#readme');
                    }
                },
                {
                    label: 'Support',
                    click: () => {
                        shell.openExternal('mailto:support@connectpure.com');
                    }
                }
            ]
        }
    ];

    if (process.platform === 'darwin') {
        template[0].submenu.unshift({
            label: 'Services',
            submenu: []
        });
        
        // Window menu
        template[3].submenu = [
            { role: 'close' },
            { role: 'minimize' },
            { role: 'zoom' },
            { type: 'separator' },
            { role: 'front' }
        ];
    }

    const menu = Menu.buildFromTemplate(template);
    Menu.setApplicationMenu(menu);
}

// App event handlers
app.whenReady().then(async () => {
    try {
        // Initialize PHP server manager
        phpServerManager = new PhpServerManager();

        // Initialize IPC handlers
        ipcHandlers = new IpcHandlers(phpServerManager);

        // Start PHP server first
        await phpServerManager.start();

        // Create window
        createWindow();

        // Create menu
        createMenu();

    } catch (error) {
        console.error('Failed to start application:', error);
        dialog.showErrorBox('Startup Error',
            'Failed to start ConCure. Please ensure PHP is installed and try again.\n\n' + error.message);
        app.quit();
    }
});

app.on('window-all-closed', () => {
    if (phpServerManager) {
        phpServerManager.stop();
    }
    if (process.platform !== 'darwin') {
        app.quit();
    }
});

app.on('activate', () => {
    if (mainWindow === null) {
        createWindow();
    }
});

app.on('before-quit', () => {
    if (phpServerManager) {
        phpServerManager.stop();
    }
});

// Security: Prevent new window creation
app.on('web-contents-created', (event, contents) => {
    contents.on('new-window', (event, navigationUrl) => {
        event.preventDefault();
        shell.openExternal(navigationUrl);
    });
});
