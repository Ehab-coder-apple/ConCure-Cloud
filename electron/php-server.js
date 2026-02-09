const { spawn, exec } = require('child_process');
const path = require('path');
const fs = require('fs');
const net = require('net');

class PhpServerManager {
    constructor() {
        this.server = null;
        this.port = 8003;
        this.host = '127.0.0.1';
        this.isRunning = false;
        this.startupTimeout = 15000; // 15 seconds
    }

    /**
     * Find available PHP executable
     */
    findPhpExecutable() {
        const isWin = process.platform === 'win32';
        const phpCommands = isWin ? ['php.exe', 'php'] : ['php'];
        
        for (const cmd of phpCommands) {
            try {
                const result = require('child_process').execSync(`${cmd} --version`, { 
                    encoding: 'utf8',
                    stdio: ['pipe', 'pipe', 'ignore'],
                    timeout: 5000
                });
                if (result.includes('PHP')) {
                    console.log(`Found PHP: ${cmd}`);
                    return cmd;
                }
            } catch (error) {
                // Continue to next command
                console.log(`PHP command '${cmd}' not found`);
            }
        }
        
        return null;
    }

    /**
     * Check if port is available
     */
    async isPortAvailable(port) {
        return new Promise((resolve) => {
            const server = net.createServer();
            
            server.listen(port, (err) => {
                if (err) {
                    resolve(false);
                } else {
                    server.once('close', () => resolve(true));
                    server.close();
                }
            });
            
            server.on('error', () => resolve(false));
        });
    }

    /**
     * Find an available port starting from the default
     */
    async findAvailablePort() {
        let port = this.port;
        while (port < this.port + 100) {
            if (await this.isPortAvailable(port)) {
                return port;
            }
            port++;
        }
        throw new Error('No available ports found');
    }

    /**
     * Get the application path
     */
    getAppPath() {
        if (process.env.NODE_ENV === 'development') {
            return path.join(__dirname, '..');
        } else {
            // In production, the app files are in the resources directory
            return path.join(process.resourcesPath, 'app');
        }
    }

    /**
     * Setup Laravel environment
     */
    async setupLaravelEnvironment() {
        const appPath = this.getAppPath();
        
        // Ensure .env file exists
        const envPath = path.join(appPath, '.env');
        const envExamplePath = path.join(appPath, '.env.example');
        
        if (!fs.existsSync(envPath) && fs.existsSync(envExamplePath)) {
            fs.copyFileSync(envExamplePath, envPath);
            console.log('Created .env file from .env.example');
        }

        // Update database path in .env
        if (fs.existsSync(envPath)) {
            let envContent = fs.readFileSync(envPath, 'utf8');
            const dbPath = path.join(appPath, 'database', 'concure.sqlite');
            
            // Update database path
            envContent = envContent.replace(
                /DB_DATABASE=.*/,
                `DB_DATABASE=${dbPath.replace(/\\/g, '/')}`
            );
            
            // Update app URL
            envContent = envContent.replace(
                /APP_URL=.*/,
                `APP_URL=http://${this.host}:${this.port}`
            );
            
            fs.writeFileSync(envPath, envContent);
            console.log('Updated .env configuration');
        }

        // Ensure database file exists
        const dbDir = path.join(appPath, 'database');
        const dbPath = path.join(dbDir, 'concure.sqlite');
        
        if (!fs.existsSync(dbDir)) {
            fs.mkdirSync(dbDir, { recursive: true });
        }
        
        if (!fs.existsSync(dbPath)) {
            fs.writeFileSync(dbPath, '');
            console.log('Created SQLite database file');
        }

        // Ensure storage directories exist
        const storageDirs = [
            'storage/app',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs'
        ];

        storageDirs.forEach(dir => {
            const fullPath = path.join(appPath, dir);
            if (!fs.existsSync(fullPath)) {
                fs.mkdirSync(fullPath, { recursive: true });
            }
        });

        console.log('Laravel environment setup complete');
    }

    /**
     * Start the PHP development server
     */
    async start() {
        if (this.isRunning) {
            console.log('PHP server is already running');
            return;
        }

        const phpPath = this.findPhpExecutable();
        if (!phpPath) {
            throw new Error('PHP not found. Please install PHP 8.1 or higher to run ConCure.');
        }

        // Find available port
        this.port = await this.findAvailablePort();
        console.log(`Using port: ${this.port}`);

        // Setup Laravel environment
        await this.setupLaravelEnvironment();

        const appPath = this.getAppPath();
        
        // Change to app directory
        process.chdir(appPath);

        return new Promise((resolve, reject) => {
            const serverCommand = [
                'artisan', 'serve',
                `--host=${this.host}`,
                `--port=${this.port}`,
                '--no-reload'
            ];

            console.log(`Starting PHP server: ${phpPath} ${serverCommand.join(' ')}`);

            this.server = spawn(phpPath, serverCommand, {
                cwd: appPath,
                stdio: ['pipe', 'pipe', 'pipe'],
                env: { ...process.env, APP_ENV: 'production' }
            });

            let resolved = false;

            this.server.stdout.on('data', (data) => {
                const output = data.toString();
                console.log(`PHP Server: ${output}`);
                
                if (output.includes('Server running') && !resolved) {
                    this.isRunning = true;
                    resolved = true;
                    resolve({
                        url: `http://${this.host}:${this.port}`,
                        port: this.port
                    });
                }
            });

            this.server.stderr.on('data', (data) => {
                const error = data.toString();
                console.error(`PHP Server Error: ${error}`);
                
                // Don't reject on warnings, only on critical errors
                if (error.includes('failed to open stream') || error.includes('Permission denied')) {
                    if (!resolved) {
                        resolved = true;
                        reject(new Error(`PHP Server Error: ${error}`));
                    }
                }
            });

            this.server.on('error', (error) => {
                console.error('Failed to start PHP server:', error);
                this.isRunning = false;
                if (!resolved) {
                    resolved = true;
                    reject(error);
                }
            });

            this.server.on('close', (code) => {
                console.log(`PHP server exited with code ${code}`);
                this.isRunning = false;
            });

            // Timeout fallback
            setTimeout(() => {
                if (!resolved) {
                    resolved = true;
                    this.isRunning = true;
                    resolve({
                        url: `http://${this.host}:${this.port}`,
                        port: this.port
                    });
                }
            }, this.startupTimeout);
        });
    }

    /**
     * Stop the PHP server
     */
    stop() {
        if (this.server) {
            console.log('Stopping PHP server...');
            this.server.kill('SIGTERM');
            
            // Force kill after 5 seconds if still running
            setTimeout(() => {
                if (this.server && !this.server.killed) {
                    console.log('Force killing PHP server...');
                    this.server.kill('SIGKILL');
                }
            }, 5000);
            
            this.server = null;
            this.isRunning = false;
        }
    }

    /**
     * Restart the PHP server
     */
    async restart() {
        this.stop();
        await new Promise(resolve => setTimeout(resolve, 2000)); // Wait 2 seconds
        return this.start();
    }

    /**
     * Get server status
     */
    getStatus() {
        return {
            isRunning: this.isRunning,
            port: this.port,
            host: this.host,
            url: `http://${this.host}:${this.port}`
        };
    }
}

module.exports = PhpServerManager;
