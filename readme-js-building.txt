// see https://moodledev.io/general/development/tools/nodejs

curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash

cd ~
nvm install && nvm use

cd /var/www/dev/online.n2ncu.org/public_html/
npm install
npm install -g grunt-cli

sudo apt install watchman

cd /var/www/dev/online.n2ncu.org/public_html/mod/biblereader/amd
grunt amd
grunt amd watch

