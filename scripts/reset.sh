#!/bin/bash
echo "Wiping archive files"
rm -Rf /srv/data/archive/files
mysql --defaults-extra-file=/etc/mysql/debian.cnf -e "truncate table files;" archive

echo "Restoring Drupal"
mysql --defaults-extra-file=/etc/mysql/debian.cnf -e "drop database drupal; create database drupal;"
mysql --defaults-extra-file=/etc/mysql/debian.cnf drupal < /srv/backups/drupal/drupal.sql
cd /srv/sites/drupal && ./cache-rebuild.sh
