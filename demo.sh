#!/bin/bash

cp -rf demo/* ../
cp demo/.htaccess ../
./piha.sh migrate up --alias=app
./piha.sh migrate up --alias=permission
