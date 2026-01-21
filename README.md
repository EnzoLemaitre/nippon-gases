# Nippon Gases

## Setup
```bash
npm install
npm run setup
```

## Composer
Install composer dependencies
```bash
composer install
```

## Bash helpers
Create the structure of a template page (scss, template-name.php, view)
```bash
npm run create-template
```
 

## FTP info
Edit ".vscode/sftp.json" file:
```json
{
    "name": "XXXX",
    "host": "XXXX",
    "protocol": "sftp",
    "port": 2222,
    "username": "XXXX",
    "password": "XXXX",
    "remotePath": "./XXXX/wp-content/themes/XXXX",
}
```

## Create htaccess / htpassword
In ssh terminal get the absolute path with
```bash
pwd
```

Add this in htaccess file
```apache
AuthType Basic
AuthName "Restricted Access"
AuthUserFile ABSOLUTE_PATH/.htpasswd
Require valid-user
```

Create htpasswd file and add user and password

## ACF fields by json files
After created json files in views/components folder, run this command:
```bash
git pull
```