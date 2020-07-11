#!/bin/bash

echo "Start deploy"
cd ~/Omtbiz.in
git pull origin master
npm i
npm run build 
#pm2 stop build/server
#pm2 start build/server
echo "Deploy end"
