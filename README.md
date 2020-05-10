# carea-datenbank

This project is still a work in progress. It consists of backend (php and mysql) and a vue.js frontend. It is allows working with a mysql database in a small convenient web application.

## Open tasks
* Fix vulnerabilities (especially SQL injection and escape html in user input)
* Translate all views to German
* Add more functionality for different use cases (update data, delete data partially, export data as pdf etc.)
* Finish existing functionality (especially inserting new data)

## PHP
The backend requires firebase/php-jwt to handle JSON web tokens.

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
