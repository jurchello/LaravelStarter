import {execSync} from 'node:child_process';

export default async function globalSetup(): Promise<void> {
    execSync('php artisan config:clear --env=testing', {
        stdio: 'inherit',
    });
    execSync('php artisan cache:clear --env=testing', {
        stdio: 'inherit',
    });
    execSync('php artisan db:seed --class=PlaywrightTestSeeder --force --env=testing', {
        stdio: 'inherit',
    });
}