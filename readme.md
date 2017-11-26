# PokeMe server 
[![Build Status](https://travis-ci.org/pokemeapp/PokeMeServer.svg?branch=master)](https://travis-ci.org/pokemeapp/PokeMeServer) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/322f1537f2b64d0d96c63783f41b983d)](https://www.codacy.com/app/realdiwin/PokeMeServer?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=pokemeapp/PokeMeServer&amp;utm_campaign=Badge_Grade) [![StyleCI Badge](https://styleci.io/repos/107265123/shield?style=flat)](https://styleci.io/repos/107265123)


This is the server for our IOS application. Basically it's an API with OAuth2 authentication, built with Laravel.

# Installation
First you have to install composer dependencies. Apply the environment configurations.
```bash
pmc install
cp .env.example .env
```
If you already installed the dependenices, than you have to generate all the laravel provided configurations and vendor libraries.
```bash
pma key:generate
pma vendor:publish
pma passport:install
pma l5-swagger:publish
```
You can check the API Docs on: `http://localhost:8080/api/documentation`
