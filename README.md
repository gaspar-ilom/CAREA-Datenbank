# CAREA-Database

CAREA is a non-profit organization from Germany: https://carea-menschenrechte.de/

This project is still a work in progress. It consists of a backend (php/mysql) and a frontend (vue.js). It allows users to work with a mysql database in a small web application.

This web application is designed for a specific use case and database making it unlikely that it is useful in other scenarios.

It is not secure to be used as is.

## Open tasks
* Fix vulnerabilities (especially SQL injection and escape html in user input)
* Translate all views to German
* Add more functionality for different use cases (update data, delete data partially, export data as pdf etc.)
* Finish existing functionality (especially inserting new data)

## PHP
The backend requires firebase/php-jwt to handle JSON web tokens. This can be installed using composer. The php code implements a RESTful api queried by the frontend to create, read, update and delete (CRUD) data stored in the mysql database.

## Mysql
This might be published later.

## Vue.js Project setup
```
npm install
```

### Compiles and hot-reloads for development
```
npm run serve
```

### Compiles and minifies for production
```
npm run build
```

### Lints and fixes files
```
npm run lint
```

### Customize configuration
See [Configuration Reference](https://cli.vuejs.org/config/).
