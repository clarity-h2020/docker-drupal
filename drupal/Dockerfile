# https://circleci.com/blog/continuous-drupal-p1-maintaining-with-docker-git-composer/
FROM drupal:8.9.1-apache


ENV DEBIAN_FRONTEND=noninteractive LANG=en_US.UTF-8 LC_ALL=C.UTF-8 LANGUAGE=en_US.UTF-8
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# RUN [ "apt-get", "install", "-qy", "--no-install-recommends", "apt-utils" ] # does not work
RUN [ "apt-get", "-q", "update" ]
RUN [ "apt-get", "-qy", "--allow-downgrades", "--allow-remove-essential", "--allow-change-held-packages", "upgrade" ]
RUN [ "apt-get", "-qy", "--allow-downgrades", "--allow-remove-essential", "--allow-change-held-packages", "dist-upgrade" ]
RUN [ "apt-get", "install", "-qy", "--allow-downgrades", "--allow-remove-essential", "--allow-change-held-packages", \
      "curl", \
      "git", \
	  "default-mysql-client", \
	  "vim", \
      "wget", \
	  "gnupg", \
	  "unzip"]

RUN touch /etc/apt/sources.list.d/pgdg.list
RUN echo 'deb http://apt.postgresql.org/pub/repos/apt/ stretch-pgdg main' > /etc/apt/sources.list.d/pgdg.list
RUN wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add -
RUN apt-get update

ADD https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.buster_amd64.deb wkhtmltox_0.12.6-1.buster_amd64.deb
RUN yes | apt install ./wkhtmltox_0.12.6-1.buster_amd64.deb
RUN [ "rm", "wkhtmltox_0.12.6-1.buster_amd64.deb" ]

RUN [ "apt-get", "install", "-qy", "--allow-downgrades", "--allow-remove-essential", "--allow-change-held-packages", \
	  "postgresql-client-10" ]
RUN [ "apt-get", "clean" ]
RUN [ "rm", "-rf", "/var/lib/apt/lists/*", "/tmp/*", "/var/tmp/*" ]

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
	php composer-setup.php && \
	mv composer.phar /usr/local/bin/composer && \
	php -r "unlink('composer-setup.php');"

RUN wget -O drush.phar https://github.com/drush-ops/drush-launcher/releases/download/0.6.0/drush.phar && \
	chmod +x drush.phar && \
	mv drush.phar /usr/local/bin/drush && \
	drush self-update

# This deletes the copy of Drupal that ships with the Docker image. You can use this if you wanted, but in this post we’re installing and tracking Drupal with Composer instead with the help of Git.
RUN rm -rf /var/www/html/*

#Copy over the custom Apache VirtualHost configuration file to tell Apache where we want to host our website within the filesystem.
COPY apache-drupal.conf /etc/apache2/sites-enabled/000-default.conf

# use user and group from host to avoid problems with files created and owned by root when using git on host
RUN groupadd -g 999 docker
RUN useradd --create-home --shell /bin/bash --uid 1002 --gid 999 pascal
RUN useradd --create-home --shell /bin/bash --uid 999 --gid 999 docker
WORKDIR /app