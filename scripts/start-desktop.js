#!/usr/bin/env node

const { spawn } = require('child_process');
const path = require('path');

console.log('🚀 Starting ConCure Desktop Application...\n');

// Check if we're in development or production
const isDev = process.env.NODE_ENV === 'development';

if (isDev) {
    console.log('🔧 Development Mode');
    console.log('Starting Vite dev server and Electron...\n');
    
    // Start Vite dev server
    const vite = spawn('npm', ['run', 'dev'], {
        stdio: 'inherit',
        shell: true
    });
    
    // Wait a bit for Vite to start, then start Electron
    setTimeout(() => {
        const electron = spawn('npm', ['run', 'electron'], {
            stdio: 'inherit',
            shell: true
        });
        
        // Handle process cleanup
        process.on('SIGINT', () => {
            console.log('\n🛑 Shutting down...');
            vite.kill();
            electron.kill();
            process.exit(0);
        });
        
        electron.on('close', () => {
            vite.kill();
            process.exit(0);
        });
        
    }, 3000);
    
} else {
    console.log('📦 Production Mode');
    console.log('Starting Electron application...\n');
    
    // Start Electron directly
    const electron = spawn('npm', ['run', 'electron'], {
        stdio: 'inherit',
        shell: true
    });
    
    electron.on('close', (code) => {
        console.log(`\n✅ Application closed with code ${code}`);
        process.exit(code);
    });
    
    // Handle process cleanup
    process.on('SIGINT', () => {
        console.log('\n🛑 Shutting down...');
        electron.kill();
        process.exit(0);
    });
}

// Handle uncaught exceptions
process.on('uncaughtException', (error) => {
    console.error('❌ Uncaught Exception:', error);
    process.exit(1);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('❌ Unhandled Rejection at:', promise, 'reason:', reason);
    process.exit(1);
});
