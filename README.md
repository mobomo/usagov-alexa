# Drupal Voice Wizard

A Drupal development environment with React and Alexa voice integration.

## Setup

### Launch

Run **`./drush`** in the root dir of the project.

### Install Drupal Modules

Prepend the command **`php composer.phar`** for the module to be installed

### Run the React app

cd to the root of the React app `/web/modules/custom/alexa2/alexa2_demo`

run `npm i`

run `npm run build`, or `npm run build:dev` to automatically rebuild when changes are made

## Cloud.gov

To launch you project on cloud.gov, run **`cf push`**
