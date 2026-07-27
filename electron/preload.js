const { contextBridge, ipcRenderer } = require('electron');

// Expose protected methods that allow the renderer process to use
// the ipcRenderer without exposing the entire object
contextBridge.exposeInMainWorld('electronAPI', {
    // App information
    getVersion: () => ipcRenderer.invoke('app:getVersion'),
    
    // Window controls
    minimize: () => ipcRenderer.invoke('window:minimize'),
    maximize: () => ipcRenderer.invoke('window:maximize'),
    close: () => ipcRenderer.invoke('window:close'),
    
    // File operations
    openFile: () => ipcRenderer.invoke('file:open'),
    saveFile: (data) => ipcRenderer.invoke('file:save', data),
    
    // System information
    getPlatform: () => process.platform,
    getArch: () => process.arch,
    
    // Notifications
    showNotification: (title, body) => ipcRenderer.invoke('notification:show', title, body),
    
    // Database operations
    backupDatabase: () => ipcRenderer.invoke('db:backup'),
    restoreDatabase: (filePath) => ipcRenderer.invoke('db:restore', filePath),
    
    // Print functionality
    print: () => ipcRenderer.invoke('print:page'),
    
    // Update functionality
    checkForUpdates: () => ipcRenderer.invoke('update:check'),
    
    // Event listeners
    onUpdateAvailable: (callback) => ipcRenderer.on('update:available', callback),
    onUpdateDownloaded: (callback) => ipcRenderer.on('update:downloaded', callback),
    
    // Remove listeners
    removeAllListeners: (channel) => ipcRenderer.removeAllListeners(channel)
});

// Expose a limited set of Node.js APIs
contextBridge.exposeInMainWorld('nodeAPI', {
    path: {
        join: (...args) => require('path').join(...args),
        dirname: (path) => require('path').dirname(path),
        basename: (path) => require('path').basename(path)
    },
    os: {
        platform: () => require('os').platform(),
        arch: () => require('os').arch(),
        homedir: () => require('os').homedir(),
        tmpdir: () => require('os').tmpdir()
    }
});

// Add desktop-specific styling
document.addEventListener('DOMContentLoaded', () => {
    // Add desktop class to body
    document.body.classList.add('desktop-app');
    
    // Add platform-specific class
    document.body.classList.add(`platform-${process.platform}`);
    
    // Disable drag and drop of files on the window
    document.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.stopPropagation();
    });
    
    document.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
    });
    
    // Handle external links
    document.addEventListener('click', (e) => {
        if (e.target.tagName === 'A' && e.target.href.startsWith('http')) {
            e.preventDefault();
            require('electron').shell.openExternal(e.target.href);
        }
    });
    
    // Add custom CSS for desktop app
    const style = document.createElement('style');
    style.textContent = `
        .desktop-app {
            user-select: none;
            -webkit-user-select: none;
        }
        
        .desktop-app input,
        .desktop-app textarea,
        .desktop-app [contenteditable] {
            user-select: text;
            -webkit-user-select: text;
        }
        
        .platform-darwin .titlebar {
            padding-left: 80px;
        }
        
        .desktop-app .no-drag {
            -webkit-app-region: no-drag;
        }
        
        .desktop-app .drag {
            -webkit-app-region: drag;
        }
        
        /* Custom scrollbars for desktop */
        .desktop-app ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        .desktop-app ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .desktop-app ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .desktop-app ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }
        }
    `;
    document.head.appendChild(style);
});

// Console logging for development
if (process.env.NODE_ENV === 'development') {
    console.log('ConCure Desktop App - Preload script loaded');
    console.log('Platform:', process.platform);
    console.log('Architecture:', process.arch);
}
