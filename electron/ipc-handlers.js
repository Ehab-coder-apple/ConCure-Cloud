const { ipcMain, dialog, shell, app, BrowserWindow, Notification } = require('electron');
const fs = require('fs');
const path = require('path');

class IpcHandlers {
    constructor(phpServerManager) {
        this.phpServer = phpServerManager;
        this.setupHandlers();
    }

    setupHandlers() {
        // App information
        ipcMain.handle('app:getVersion', () => {
            return app.getVersion();
        });

        ipcMain.handle('app:getName', () => {
            return app.getName();
        });

        // Window controls
        ipcMain.handle('window:minimize', () => {
            const window = BrowserWindow.getFocusedWindow();
            if (window) window.minimize();
        });

        ipcMain.handle('window:maximize', () => {
            const window = BrowserWindow.getFocusedWindow();
            if (window) {
                if (window.isMaximized()) {
                    window.unmaximize();
                } else {
                    window.maximize();
                }
            }
        });

        ipcMain.handle('window:close', () => {
            const window = BrowserWindow.getFocusedWindow();
            if (window) window.close();
        });

        // File operations
        ipcMain.handle('file:open', async () => {
            const result = await dialog.showOpenDialog({
                properties: ['openFile'],
                filters: [
                    { name: 'All Files', extensions: ['*'] },
                    { name: 'Images', extensions: ['jpg', 'jpeg', 'png', 'gif'] },
                    { name: 'Documents', extensions: ['pdf', 'doc', 'docx'] },
                    { name: 'Database', extensions: ['sqlite', 'db'] }
                ]
            });

            if (!result.canceled && result.filePaths.length > 0) {
                return result.filePaths[0];
            }
            return null;
        });

        ipcMain.handle('file:save', async (event, data) => {
            const result = await dialog.showSaveDialog({
                filters: [
                    { name: 'All Files', extensions: ['*'] },
                    { name: 'JSON', extensions: ['json'] },
                    { name: 'CSV', extensions: ['csv'] },
                    { name: 'PDF', extensions: ['pdf'] }
                ]
            });

            if (!result.canceled && result.filePath) {
                try {
                    fs.writeFileSync(result.filePath, data);
                    return { success: true, path: result.filePath };
                } catch (error) {
                    return { success: false, error: error.message };
                }
            }
            return { success: false, error: 'Save cancelled' };
        });

        // Notifications
        ipcMain.handle('notification:show', (event, title, body) => {
            if (Notification.isSupported()) {
                new Notification({
                    title: title,
                    body: body,
                    icon: path.join(__dirname, 'assets', 'icon.png')
                }).show();
                return true;
            }
            return false;
        });

        // Database operations
        ipcMain.handle('db:backup', async () => {
            const appPath = this.phpServer.getAppPath();
            const dbPath = path.join(appPath, 'database', 'concure.sqlite');
            
            if (!fs.existsSync(dbPath)) {
                return { success: false, error: 'Database file not found' };
            }

            const result = await dialog.showSaveDialog({
                defaultPath: `concure-backup-${new Date().toISOString().split('T')[0]}.sqlite`,
                filters: [
                    { name: 'SQLite Database', extensions: ['sqlite'] },
                    { name: 'All Files', extensions: ['*'] }
                ]
            });

            if (!result.canceled && result.filePath) {
                try {
                    fs.copyFileSync(dbPath, result.filePath);
                    return { success: true, path: result.filePath };
                } catch (error) {
                    return { success: false, error: error.message };
                }
            }
            return { success: false, error: 'Backup cancelled' };
        });

        ipcMain.handle('db:restore', async (event, filePath) => {
            if (!filePath) {
                const result = await dialog.showOpenDialog({
                    properties: ['openFile'],
                    filters: [
                        { name: 'SQLite Database', extensions: ['sqlite'] },
                        { name: 'All Files', extensions: ['*'] }
                    ]
                });

                if (result.canceled || result.filePaths.length === 0) {
                    return { success: false, error: 'No file selected' };
                }
                filePath = result.filePaths[0];
            }

            const appPath = this.phpServer.getAppPath();
            const dbPath = path.join(appPath, 'database', 'concure.sqlite');

            try {
                // Create backup of current database
                const backupPath = path.join(appPath, 'database', `concure-backup-${Date.now()}.sqlite`);
                if (fs.existsSync(dbPath)) {
                    fs.copyFileSync(dbPath, backupPath);
                }

                // Restore from selected file
                fs.copyFileSync(filePath, dbPath);
                
                return { success: true, backupPath: backupPath };
            } catch (error) {
                return { success: false, error: error.message };
            }
        });

        // Print functionality
        ipcMain.handle('print:page', () => {
            const window = BrowserWindow.getFocusedWindow();
            if (window) {
                window.webContents.print({
                    silent: false,
                    printBackground: true,
                    deviceName: '',
                    color: true,
                    margins: {
                        marginType: 'printableArea'
                    },
                    landscape: false,
                    scaleFactor: 100
                });
                return true;
            }
            return false;
        });

        // PHP Server controls
        ipcMain.handle('server:status', () => {
            return this.phpServer.getStatus();
        });

        ipcMain.handle('server:restart', async () => {
            try {
                const result = await this.phpServer.restart();
                return { success: true, ...result };
            } catch (error) {
                return { success: false, error: error.message };
            }
        });

        // System information
        ipcMain.handle('system:info', () => {
            return {
                platform: process.platform,
                arch: process.arch,
                nodeVersion: process.version,
                electronVersion: process.versions.electron,
                chromeVersion: process.versions.chrome
            };
        });

        // External links
        ipcMain.handle('shell:openExternal', (event, url) => {
            shell.openExternal(url);
        });

        // Show item in folder
        ipcMain.handle('shell:showItemInFolder', (event, fullPath) => {
            shell.showItemInFolder(fullPath);
        });

        // Application data directory
        ipcMain.handle('app:getPath', (event, name) => {
            return app.getPath(name);
        });

        // Check for updates (placeholder for future implementation)
        ipcMain.handle('update:check', () => {
            // TODO: Implement auto-updater
            return { available: false, version: app.getVersion() };
        });

        // Error reporting
        ipcMain.handle('error:report', (event, error) => {
            console.error('Renderer process error:', error);
            
            // In production, you might want to send this to a logging service
            if (process.env.NODE_ENV === 'production') {
                // TODO: Implement error reporting service
            }
            
            return true;
        });

        // Log messages from renderer
        ipcMain.handle('log:message', (event, level, message) => {
            console[level](`Renderer: ${message}`);
        });
    }
}

module.exports = IpcHandlers;
