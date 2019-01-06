WELCOME
=======

This is Yii 2 Basic Project Template with : 
- Template AdminLTE
- Yii2-user
- Yii2-rbac

DIRECTORY STRUCTURE
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      mail/               contains view files for e-mails
      models/             contains model classes
      runtime/            contains files generated during runtime
      tests/              contains various tests for the basic application
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources



REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 5.4.0.


INSTALLATION
------------

Download as zip file. Extract to your webroot folder. Then run composer update.

- yii migrate/up --migrationPath=@vendor/dektrium/yii2-user/migrations
- yii user/create [email] [username] [password]
- require mdmsoft/yii2-admin "~2.0"
- yii migrate --migrationPath=@mdm/admin/migrations
- yii migrate --migrationPath=@yii/rbac/migrations

CREDIT
------
Haezal Musa
