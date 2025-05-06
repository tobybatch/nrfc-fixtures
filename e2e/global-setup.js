// tests/global-setup.js
const { execSync } = require('child_process');

module.exports = async () => {
    // Run the PHP script to set up the database
    execSync('php tests/TestDatabaseSetup.php', { stdio: 'inherit' });
};