## PUP LOPEZ CSC Voting System

### Setup

-   `cd 'projectname'`
-   `composer install` (requires Composer: https://getcomposer.org)
-   Rename `.env.example` to `.env`
-   Configure `.env`, database, url, appname
-   `php artisan key:generate`
-   `php artisan storage:link`
-   `php artisan migrate:fresh --seed` we make sure this is the first time
-   `php artisan serve` to start the app

### Queue work

-   `php artisan queue:work`

### Routes

See .env `SPA_STUDENT_URL` `SPA_ADMIN_URL`

-   `/`
-   `/pupadmin`

### Database Seeds

-   Administrator: email = admin@admin.com, password = 1234567890
