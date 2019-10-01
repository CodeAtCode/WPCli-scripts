# WPCli-scripts
[![License](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0)   
Our scripts for WP-CLI

## Other resources

* What you can do with WP-CLI - http://mte90.tech/Talk-WPCLI/
* Get the id of a post type by the url - https://github.com/CodeAtCode/wp-cli-getbyurl
* WPDB-Status - https://github.com/CodeAtCode/WPDB-Status

## Suggestion

### PHP binaries that run in the WordPress context

```
#!/bin/env wp eval-file
<?php
echo get_bloginfo('name');
```