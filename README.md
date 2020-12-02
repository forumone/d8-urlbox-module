# Drupal 8 Module for URLBox

## Overview

A Drupal 8 module for providing screenshots and PDF generation.

## Installation and setup

1) Add this repository to your `composer.json`. You can find the general template for this [here](https://getcomposer.org/doc/05-repositories.md#using-private-repositories).
2) Install your dependencies: `f1 composer install`
3) Set up your environmental variables. You can find more information about that in the section below.
4) Add `export pdf` & `export png` permissions to the relevant roles in the Drupal administrator interface.

## Creating queries for the service

URLBox has a handy tool for creating queries in their dashboard. As this tool is compatible with the that format it is recommended to build new queries in the URLBox dashboard. This can be found [here](https://urlbox.io/dashboard) (Account Required).

## Routes

This service has two valid routes. One for creating and downloading PDFs and another for PNGs. These are:

* `/screenshot_service/png`
* `/screenshot_service/pdf`

The queries to these endpoints follow the same format as in [URLBox's Documentation](https://urlbox.io/docs). Be aware that these options must be explicitly enabled in the environmental variables. These endpoints return binary data (the image) and text in the event of and error.

## Webcrawlers

Its advisable to add the `/screenshot_service/` path to your site's `robots.txt` as URLBox _is_ a service with a quota.

## Permissions
Two permissions exist and must be added to the anonymous users role to be able to download screenshots and PDFs. These permissions are:

* `export pdf`<br>
Allows users to create and download PDFs using the URLBox service.

* `export png`<br>
Allows users to create and download PNGs using the URLBox service.

## Environmental Variables

* `URLBOX_API_KEY`<br>
Can be found in your URLBox account dashboard.

* `URLBOX_SECRET`<br>
Can be found in your URLBox account dashboard.

* `URLBOX_ALLOWED_HOSTS`<br>
Hosts which may be passed to the URLBox service. In thr format of: `'google.com, microsoft.com'`

* `URLBOX_ALLOWED_OPTIONS`<br>
Options which may be passed to the service. In the format of: `'url, selector, height'`. A list of valid options can be found in the URLBox documentation.