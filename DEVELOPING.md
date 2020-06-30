<!---
Copyright 2018 The Subscribe with Google Authors. All Rights Reserved.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

      http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS-IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
-->

# Development on SwG WordPress Plugin

## How to get started

Before you start developing in SwG WordPress Plugin, check out these resources:
* [CONTRIBUTING.md](./CONTRIBUTING.md) has details on various ways you can contribute to the SwG WordPress Plugin.
* If you're developing in SwG WordPress Plugin, you should read the [Contributing code](./CONTRIBUTING.md#contributing-code).

## Setup

TODO: Expand this...
- Install a working WordPress 4.7+ site with PHP 5.6+
- Install Composer
- Install PHP dependencies with `composer`
- Install Nodejs
- Install Yarn
- Install JavaScript dependencies with `yarn`
- Compile JavaScript and CSS with `yarn watch`
- Now the plugin is ready to test on your WP site!

### Optional: Using VS Code
- Install VS Code
- Open the plugin directory in VS Code
- Install PHP Intelephense plugin (`bmewburn.vscode-intelephense-client`)
- Install PHP Sniffer plugin (`wongjn.php-sniffer`)
- Install ESLint plugin (`dbaeumer.vscode-eslint`)

## Bash commands

Use the following Bash commands:

| Command                                                                 | Description                                                           |
| ----------------------------------------------------------------------- | --------------------------------------------------------------------- |
| `yarn watch`                                                       | Compiles JS and CSS, then watches for changes.                      |
| `yarn test`                                                       | Runs JS tests.                      |
| `composer test`                                                       | Runs PHP tests.                      |
| `composer lint`                                                             | Validates PHP with PHPCS.                              |
| `composer lint-fix`                                                     | Automatically fixes (most) PHP linting issues with PHPCS.|

## Repository Layout
<pre>
  assets/         - source code and tests for JS and CSS
  gulp-tasks/     - build infrastructure
  includes/       - source code for PHP
  tests/          - tests for PHP
</pre>

## [Code of conduct](./CODE_OF_CONDUCT.md)
