#!/usr/bin/env node

// Simple launcher for ConCure Desktop App
// This script can be used when npm dependencies are not available

const { spawn } = require('child_process');
const path = require('path');

console.log('🚀 Starting ConCure Desktop App...');

// Check if electron is available
try {
    const electron = spawn('npx', ['electron', '.'], {
        stdio: 'inherit',
        shell: true
    });
    
    electron.on('error', (error) => {
        console.error('❌ Failed to start Electron:', error.message);
        console.log('💡 Please run: npm install');
    });
    
    electron.on('close', (code) => {
        console.log(`✅ Application closed with code ${code}`);
    });
    
} catch (error) {
    console.error('❌ Error:', error.message);
    console.log('💡 Please ensure Node.js and npm are installed');
}
