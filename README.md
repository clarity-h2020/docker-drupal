# docker-drupal

[Drupal 8](https://www.drupal.org/) Project for implementing the [CLARITY](https://clarity-h2020.eu/) Climate Services Information System ([CSIS](https://github.com/clarity-h2020/csis/)).

## Description 

This repository contains the general configuration for CSIS docker containers deployed at [CSIS Development System](https://github.com/clarity-h2020/csis#csis-development-system) and [CSIS Production System](https://github.com/clarity-h2020/csis#csis-production-system) virtual servers. 

The actual drupal site configuration which is mapped to the *bind-mount* volume `drupal-data` is stored in the separate **private** [clarity-csis-drupal](https://scm.atosresearch.eu/ari/clarity-csis-drupal) repository. This repository is mainly used for [synchronising](#synchronisation-between-dev-and-prod) configuration and content between the [development](https://csis-dev.myclimateservice.eu/) and [production](https://csis.myclimateservice.eu/) system.

## Implementation 

CLARTIY CSIS is implemented as *dockerized* Drupal 8 with PostGIs database. The initial configuration is based on [Continuous Drupal](https://circleci.com/blog/electron-builds/). The main artefacts of the repository are

- [drupal/Dockerfile](https://github.com/clarity-h2020/docker-drupal/blob/dev/drupal/Dockerfile): Customised **Drupal 8** Docker Container for CLARITY CSIS
- [postgis/Dockerfile](https://github.com/clarity-h2020/docker-drupal/blob/dev/postgis/Dockerfile): Customised **PostGIS 10** Docker Container for CLARITY CSIS
- [docker-compose.yml](https://github.com/clarity-h2020/docker-drupal/blob/dev/docker-compose.yml): for configuring deploying the customised containers

## Deployment

CLARTIY CSIS is deployed as docker containers `csis-drupal` and `csis-postgis` on [CSIS Development System](https://github.com/clarity-h2020/csis#csis-development-system) and [CSIS Production System](https://github.com/clarity-h2020/csis#csis-production-system) virtual servers. A separate [nginx-proxy](https://github.com/clarity-h2020/docker-compose-letsencrypt-nginx-proxy-companion/tree/csis-dev.ait.ac.at) exposes the internal Drupal Apache service to csis-dev.myclimateservice.eu and csis.myclimateservice.eu.

### Building the Docker Images

Building the drupal and postgis docker image is straightforward:

```sh
cd /docker/100-csis
docker-compose build
```

### Configuring and starting the containers
Containers are managed via the customised [docker-compose.yml](https://github.com/clarity-h2020/docker-drupal/blob/dev/docker-compose.yml). The actual configuration is maintained in an `.env` file while [.env.sample](https://github.com/clarity-h2020/docker-drupal/blob/dev/.env.sample) can be used as blueprint of this configuration file. Variables in this file will be substituted into docker-compose.yml.

The configuration on the development and production system is identical, except for the environment variables `VIRTUAL_HOST` and `LETSENCRYPT_HOST` required by [nginx-proxy](https://github.com/clarity-h2020/docker-compose-letsencrypt-nginx-proxy-companion/tree/csis-dev.ait.ac.at). Example for  development system:

```sh
VIRTUAL_HOST=csis-dev.ait.ac.at,csis-dev.clarity.cismet.de,csis-dev.myclimateservice.eu
LETSENCRYPT_HOST=csis-dev.ait.ac.at,csis-dev.clarity.cismet.de,csis-dev.myclimateservice.eu
```

The containers can be started with:

```sh
docker-compose up -d --force-recreate --remove-orphans
```

### Data Volumes

Data of different containers is stored in the following **bind-mount** volumes:

- Drupal site configuration  in `/docker/100-csis/drupal-data/`
- postgres database in `/docker/100-csis/postgresql_data/`

## Tests and Monitoring

### Integration Tests

UI Integration Tests both for the deployed development and production system are performed with help of [cypress.io](https://www.cypress.io/) and executed on [Jenkins CI](https://ci.cismet.de/view/CLARITY/). The test specifications are maintained in repository [csis-technical-validation](https://github.com/clarity-h2020/csis-technical-validation/) in branches [csis-cypress](https://github.com/clarity-h2020/csis-technical-validation/tree/csis-cypress) and [csis-dev-cypress](https://github.com/clarity-h2020/csis-technical-validation/tree/csis-dev-cypress). If any of the test fails, the CI system will automatically post a new [issue](https://github.com/clarity-h2020/csis-technical-validation/issues?q=is%3Aissue+is%3Aopen+label%3ACI) in the repository.

### Service Monitoring

CSIS Services [are monitored](https://health-check.clarity.cismet.de/) with help of [statping](https://github.com/statping/statping). The monitoring services are also [integrated](https://github.com/clarity-h2020/csis-technical-validation/tree/health-check-cypress) in [Jenkins CI](https://ci.cismet.de/view/CLARITY/) and a [new issue](https://github.com/clarity-h2020/csis-technical-validation/issues?q=is%3Aissue+is%3Aopen+label%3ACI) is posted in repository [csis-technical-validation](https://github.com/clarity-h2020/csis-technical-validation/) when one of the monitored services fails.

## Backups

Apart from the general virtual server backups performed by [AIT](https://www.ait.ac.at/) data center, daily full backups of the CSIS data volumes stored at `/docker/100-csis` are saved in `/docker/100-csis-daily.tar.gz`. Additional incremental backups can be performed with help of [docker-duplicity](https://github.com/clarity-h2020/docker-duplicity/).

## Upgrading

Upgrading the system is a multi-step process that involves not only updating the container images but also the Drupal core system as such as well custom and integrated apps and modules. 

### Docker Images

Stop and remove drupal containers:

```sh
cd /docker/100-csis
docker-compose down
```

Manually trigger **incremental** [duplicity backup](https://github.com/clarity-h2020/docker-duplicity):

```sh
cd /docker/999-duplicity
docker-compose up
```
**Hint:** You can restore **the latest** backup-set with `docker-compose up -f restore.yml` to `./restore` directory.

Edit [Drupal Dockerfile](https://github.com/clarity-h2020/docker-drupal/blob/dev/drupal/Dockerfile) and change base image to [docker-drupal](https://hub.docker.com/_/drupal/) new version. E.g. `FROM drupal:8.9.1-apache` for csis-drupal and `FROM postgis/postgis:10-3.0` for csis-postgis.

**Hint:**  This does **not** update the existing CSIS Drupal Core, since the actual Drupal system lives in a **host-mounted** `./drupal-data` volume! Therefore a **manual update** inside the new **running container** is required. See [upgrading Drupal Core and Modules](#Drupal-Core-and-Modules).

Edit [compose file](https://github.com/clarity-h2020/docker-drupal/blob/dev/docker-compose.yml) and change **custom images** tags, e.g. to `8.9.1-apache` and `csis-postgis:10-3.0`, in order to be able to revert to the previous image:

```yml
  drupal:
    image: csis-drupal:8.9.1-apache
    build:
      context: ./drupal
    container_name: ${COMPOSE_PROJECT_NAME}-drupal
```
Also upgrade [csis-postgis](https://github.com/clarity-h2020/docker-drupal/blob/dev/postgis/Dockerfile) image, if necessary.

```yml
  drupal-db:
    image: csis-postgis:10-3.0
    build:
      context: ./postgis
    container_name: ${COMPOSE_PROJECT_NAME}-postgis
```

Build our custom csis-drupal docker images:

```sh
cd /docker/100-csis
docker-compose build
```

Recreate containers:

```sh
docker-compose up -d --force-recreate --remove-orphans
```

Furthermore any changes to the docker and compose files have to be committed and a new [release](https://github.com/clarity-h2020/docker-drupal/releases) has to be made.

### Drupal Core and Modules

The actual [site configuration](https://scm.atosresearch.eu/ari/clarity-csis-drupal) which includes the Drupal core system and the different modules lives in a separate **bind-mount** volume and has to be updated separately.

sh into csis-drupal container:

```sh
sudo su docker
docker exec -it --user 999 csis-drupal bash
```

Backup configuration on private [clarity-csis-drupal](https://scm.atosresearch.eu/ari/clarity-csis-drupal) repository:

```sh
# backup configuration
drush cex
git add --all
git commit -a -m "commit message"
git push
## Username for 'https://scm.atosresearch.eu'
## Password for 'https://username@scm.atosresearch.eu':
```

In general, Drupal Core and Modules can be updated with help of [drush](https://www.drush.org/) and [composer](https://getcomposer.org/):

```sh
composer update
drush updb
```

However, some of the modules had to be [patched](https://scm.atosresearch.eu/ari/clarity-csis-drupal/tree/dev/patches), so that the update process is not so straightforward. For more information refer to [this issue ](https://github.com/clarity-h2020/docker-drupal/issues/148).

###  Custom Modules and Apps

Currently, the following custom modules and integrated apps are deployed together with CSIS:

- [CSIS Helpers Drupal Module](https://github.com/clarity-h2020/csis-helpers-module)
- [CSIS Drupal Theme](https://github.com/clarity-h2020/clarity-theme)
- [Map Component](https://github.com/clarity-h2020/map-component)
- [Simple Table Component](https://github.com/clarity-h2020/simple-table-component)
- [Scenario Analysis](https://github.com/clarity-h2020/scenario-analysis/issues)

They have to be updated separately. Instruction for doing so are provided in the respective `readme.md` files in the repository root.

## Synchronisation between DEV and PROD

The synchronisation between the development and production system consists of two separate steps for synchronising content and configuration.

### Importing/Exporting configuration

**NOTE:** This issue discusses how to migrate configuration (e.g. site configuration like caching, configuration of Views and display & form modes, available fields in content types, ...) **but not** actual content (node A, Tax. term B). That is adressed in a separate issue (#147).

We're using the [Configuration Split](https://www.drupal.org/project/config_split) module to manage, which configuration will be enabled on the Dev or Prod server only and **which will be enabled on both. Configuration will always be imported/exported completely** as yaml files, but into different directories (inside `/app/config/`), which will (depending on the split settings) be used or ignored on the two environments. That way the configuration will always be completely available in our private Git repo. 

#### Managing splits
Splits can be managed at `/admin/config/development/configuration/config-split`. We will have one split called `dev_split`. On the Dev server this split needs to be active, while on the Prod server it has to be deactivated. That will be handled in the settings.php by setting
`$config['config_split.config_split.dev_split']['status']` either "TRUE" or "FALSE".
<br>

#### The process in general
1) Changes will be made on Dev server (e.g. a new field or a module will be enabled/disabled)
2) View all the changes at `/admin/config/development/configuration` and: 
2.a) If this change should not take effect on Prod, then the split needs to be configured to exclude the change (see _Managing splits_ above)
2.b) If this change should take effect on Prod, the split doesn't require any changes.
3) via terminal export the configuration using `drush config-split:export` (short: `drush csex`) <br> **Note:** When asked whether to perform a normal (including filters) export, select `YES`. Confirm export by viewing pending configuration changes (as shown in step 2). This should now be empty.
4) Push configuration to private repository and pull on Prod server
5) Run `composer install` first if composer.json file changed, otherwise configuration import might fail
6) If there are any new modules added by composer, they now need to be installed in Drupal via the "Extend" page in the Backend UI
7) Import configuration via `drush config-split:import` (short: `drush csim`)
8) check if updates in DB are necessary with `drush updb`
9) clear cache with `drush cr`

#### Limitations
Due to the difficult selection possibilities in the UI of this module and unpredictable relationships between individual configuration files, it is not feasible to use this module for short-term differences in the configuration of both servers (like "new field was added but we don't want to yet include it in the upcoming synchronization"). It's difficult to achieve and very error-prone. 
This module is meant to be used for permanent differences in the config (like always disabling the Devel module  and it sub-modules on the Prod server for security reasons).

### Importing/Exporting content

NOTE: This issue discusses how to migrate actual content (nodes like data packages, resources etc. and taxonomy terms like the EU-GL taxonomy for example). Synchronizing configuration is discussed in issue #138 and **needs to be completed BEFORE synchronizing content.**

### Overview
For synchronizing content we use the [Migrate ](https://www.drupal.org/docs/8/api/migrate-api/migrate-api-overview)module, which is part of the Drupal Core and some of its additional modules (Migrate Plus and Migrate Tools). The content is pulled from the Dev via REST-Views (all called "Drupal-shared ...", which means that Dev needs to be accessible via HTTP.

The general workflow will be that certain nodes (GL-step templates, data packages and all their referenced entities) and all of the taxonomies will be exported in Dev and imported in Prod. The following actions are performed:
- New elements will be created in Prod
- Existing elements will be updated
- Elements removed in Dev will also be removed in Prod
- For synchronizing **only published** elements will be taken into account

#### Managing the migration configuration files
The target site (in our case the production system) needs for each content type the migration configuration stored in a .yml file (these .yml files are stored and synchronized with the other configuration .yml files, see #138). To add a new migration configuration:
1) Create the yml file (use existing ones for reference)
2) Add the file in the Drupal backend UI at _.../admin/config/development/configuration/single/import_ (as configuration type select "Migration")
3) Synchronize configuration between Dev and Prod (#138)
4) If an existing migration needs to be changed, use the "Single item export" feature under same link as in (2) and export desired migration including the migration UUID, which will be needed for the re-import later after changes to file are done

#### Synchronization process
1) in the Docker-container of Prod run either the provided bash script to synchronize all content in the correct order
2) or synchronize individual content types using drush (make sure to migrate referenced items first!):
`drush mim [ID_of_Migration_file] --update`

#### Things to know
- `--update` argument can be omitted. In that case existing items will not be updated but instead be skipped
- in case a migration gets stuck, its status can be reset with `drush mrs [ID_of_Migration_file]`
- should a migration fail (e.g. content was removed in Prod and can't be found for lookup process) the migration can be rolled back with `drush mr [ID_of_Migration_file]`
- `--all` argument can be used with `drush mr` or `drush mrs` to take affect on all migrations and not just one
- `drush ms` lists all available migrations and their status and other details (can also be seen in Drupal BE)
- under **Structure** -> **Migrations** (.../admin/structure/migrate) all migrations can also be viewed in the BE. It's also possible to remove individual migrations there.

## License
 
MIT © [Austrian Institute Of Technology](https://www.ait.ac.at/), [cismet GmbH](https://github.com/cismet) and [Smart Cities Consulting](https://www.smartcitiesconsulting.eu/)