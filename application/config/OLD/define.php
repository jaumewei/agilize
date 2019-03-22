<?php defined('APP_EPK') or die;

return [
        /**
         * Polymorphik root path
         * [application_path]
         */
        //'APP_ROOT', '../application',
        /**
         * Project root path
         * [projects_path]
         */
        //'APP_PROJECTS', '../projects',
        /**
         * Polymorphik core modules
         * [application_path]/modules/
         */
        'APP_MODULES', sprintf('%s/modules',APP_ROOT) ,
        /**
         * Plugins folder
         * [application_path]/components/plugins
         */
        'APP_PLUGINS', sprintf('%s/components/plugins',APP_ROOT) ,
        /**
         * Render Themes folder
         * [application_path]/themes
         */
        'APP_THEMES', sprintf('%s/themes',APP_ROOT) ,
];