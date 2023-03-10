symfony6-test
==================
**Prerequisite**
------------

- PHP greater version from 7.
- mysql installed OR Xamp, Wammp installed.

**Installation**
------------

- Clone the repository from github

```
$ git clone https://github.com/muzammal-invozone/symfony-test.git <your-path-to-install>
$ cd <your-path-to-install>
```
- open up the .env files and change the variables values
```
FRUITS_API_URL
DATABASE_URL
EMAIL_FROM
EMAIL_TO
SMTP_DNS
```

- Use Composer to get the dependencies

```
$ composer install
```

-  Set up the Database and load example dates

```
$ php bin/console doctrine:database:create
$ php bin/console doctrine:migrations:migrate
```

- Run a built-in web server

```
$ symfony server:start
```

- And type in your favourite browser:

```
http://127.0.0.1:8000
```
