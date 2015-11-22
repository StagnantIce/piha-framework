#!/bin/bash

cp -rf demo/* ../
cp demo/.htaccess ../
./deploy/migrate.sh up --alias=app
