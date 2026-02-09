const { Tray, Menu, nativeImage, Notification, app, shell } = require('electron');
const path = require('path');
const fs = require('fs');

class DesktopFeatures {
    constructor(mainWindow, phpServerManager) {
        this.mainWindow = mainWindow;
        this.phpServer = phpServerManager;
        this.tray = null;
        this.isQuitting = false;
        
        this.setupTray();
        this.setupNotifications();
        this.setupAutoLaunch();
    }

    /**
     * Setup system tray
     */
    setupTray() {
        // Create tray icon
        const iconPath = path.join(__dirname, 'assets', 'icon.png');
        let trayIcon;
        
        if (fs.existsSync(iconPath)) {
            trayIcon = nativeImage.createFromPath(iconPath);
        } else {
            // Create a simple icon if file doesn't exist
            trayIcon = nativeImage.createEmpty();
        }
        
        // Resize icon for tray
        if (!trayIcon.isEmpty()) {
            trayIcon = trayIcon.resize({ width: 16, height: 16 });
        }
        
        this.tray = new Tray(trayIcon);
        
        // Set tooltip
        this.tray.setToolTip('ConCure Clinic Management');
        
        // Create context menu
        this.updateTrayMenu();
        
        // Handle tray click
        this.tray.on('click', () => {
            if (this.mainWindow) {
                if (this.mainWindow.isVisible()) {
                    this.mainWindow.hide();
                } else {
                    this.mainWindow.show();
                    this.mainWindow.focus();
                }
            }
        });
        
        this.tray.on('right-click', () => {
            this.tray.popUpContextMenu();
        });
    }

    /**
     * Update tray context menu
     */
    updateTrayMenu() {
        if (!this.tray) return;
        
        const serverStatus = this.phpServer.getStatus();
        
        const contextMenu = Menu.buildFromTemplate([
            {
                label: 'ConCure Clinic Management',
                enabled: false
            },
            { type: 'separator' },
            {
                label: `Server: ${serverStatus.isRunning ? 'Running' : 'Stopped'}`,
                enabled: false
            },
            {
                label: serverStatus.isRunning ? `Port: ${serverStatus.port}` : 'Not Running',
                enabled: false
            },
            { type: 'separator' },
            {
                label: 'Show Application',
                click: () => {
                    if (this.mainWindow) {
                        this.mainWindow.show();
                        this.mainWindow.focus();
                    }
                }
            },
            {
                label: 'Hide Application',
                click: () => {
                    if (this.mainWindow) {
                        this.mainWindow.hide();
                    }
                }
            },
            { type: 'separator' },
            {
                label: 'Quick Actions',
                submenu: [
                    {
                        label: 'New Patient',
                        click: () => {
                            this.openQuickAction('/patients/create');
                        }
                    },
                    {
                        label: 'Today\'s Appointments',
                        click: () => {
                            this.openQuickAction('/appointments/today');
                        }
                    },
                    {
                        label: 'Financial Summary',
                        click: () => {
                            this.openQuickAction('/financial/summary');
                        }
                    }
                ]
            },
            { type: 'separator' },
            {
                label: 'Server Controls',
                submenu: [
                    {
                        label: 'Restart Server',
                        click: async () => {
                            try {
                                await this.phpServer.restart();
                                this.showNotification('Server Restarted', 'ConCure server has been restarted successfully.');
                                this.updateTrayMenu();
                            } catch (error) {
                                this.showNotification('Server Error', 'Failed to restart server: ' + error.message);
                            }
                        }
                    },
                    {
                        label: 'Open Data Directory',
                        click: () => {
                            const appPath = this.phpServer.getAppPath();
                            shell.showItemInFolder(path.join(appPath, 'database'));
                        }
                    }
                ]
            },
            { type: 'separator' },
            {
                label: 'Backup Database',
                click: () => {
                    this.mainWindow.webContents.send('trigger-backup');
                }
            },
            {
                label: 'Settings',
                click: () => {
                    this.openQuickAction('/settings');
                }
            },
            { type: 'separator' },
            {
                label: 'About ConCure',
                click: () => {
                    this.showAboutDialog();
                }
            },
            {
                label: 'Quit ConCure',
                click: () => {
                    this.isQuitting = true;
                    app.quit();
                }
            }
        ]);
        
        this.tray.setContextMenu(contextMenu);
    }

    /**
     * Open quick action in main window
     */
    openQuickAction(route) {
        if (this.mainWindow) {
            this.mainWindow.show();
            this.mainWindow.focus();
            this.mainWindow.webContents.executeJavaScript(`
                if (window.location.pathname !== '${route}') {
                    window.location.href = '${route}';
                }
            `);
        }
    }

    /**
     * Setup notifications
     */
    setupNotifications() {
        // Check if notifications are supported
        if (!Notification.isSupported()) {
            console.log('Notifications are not supported on this system');
            return;
        }

        // Request permission (automatically granted on desktop)
        console.log('Notifications are supported and enabled');
    }

    /**
     * Show notification
     */
    showNotification(title, body, options = {}) {
        if (!Notification.isSupported()) {
            console.log(`Notification: ${title} - ${body}`);
            return;
        }

        const notification = new Notification({
            title: title,
            body: body,
            icon: path.join(__dirname, 'assets', 'icon.png'),
            silent: options.silent || false,
            urgency: options.urgency || 'normal'
        });

        notification.on('click', () => {
            if (this.mainWindow) {
                this.mainWindow.show();
                this.mainWindow.focus();
            }
            
            if (options.action) {
                options.action();
            }
        });

        notification.show();
        return notification;
    }

    /**
     * Setup auto-launch (future feature)
     */
    setupAutoLaunch() {
        // This would integrate with auto-launch libraries
        // For now, just log the capability
        console.log('Auto-launch capability initialized');
    }

    /**
     * Show about dialog
     */
    showAboutDialog() {
        const { dialog } = require('electron');
        
        dialog.showMessageBox(this.mainWindow, {
            type: 'info',
            title: 'About ConCure',
            message: 'ConCure Clinic Management System',
            detail: `Version: ${app.getVersion()}
Platform: ${process.platform}
Electron: ${process.versions.electron}
Node.js: ${process.versions.node}

Developed by Connect Pure
A comprehensive clinic management solution for healthcare providers.

© 2024 Connect Pure. All rights reserved.`,
            buttons: ['OK', 'Visit Website'],
            defaultId: 0
        }).then((result) => {
            if (result.response === 1) {
                shell.openExternal('https://connectpure.com');
            }
        });
    }

    /**
     * Handle window close event
     */
    handleWindowClose(event) {
        if (!this.isQuitting && process.platform === 'darwin') {
            event.preventDefault();
            this.mainWindow.hide();
            
            // Show notification on first minimize
            if (!this.hasShownMinimizeNotification) {
                this.showNotification(
                    'ConCure is still running',
                    'The application has been minimized to the system tray. Click the tray icon to restore.',
                    { silent: true }
                );
                this.hasShownMinimizeNotification = true;
            }
        }
    }

    /**
     * Schedule notifications (future feature)
     */
    scheduleNotification(title, body, delay) {
        setTimeout(() => {
            this.showNotification(title, body);
        }, delay);
    }

    /**
     * Update server status in tray
     */
    updateServerStatus() {
        this.updateTrayMenu();
    }

    /**
     * Cleanup tray
     */
    destroy() {
        if (this.tray) {
            this.tray.destroy();
            this.tray = null;
        }
    }
}

module.exports = DesktopFeatures;
