[![Packagist Latest Stable Version](https://poser.pugx.org/chekote/behat-retry-extension/version.svg)](https://packagist.org/packages/chekote/behat-retry-extension)
[![Packagist Latest Unstable Version](https://poser.pugx.org/chekote/behat-retry-extension/v/unstable.svg)](https://packagist.org/packages/chekote/behat-retry-extension)
[![Packagist Total Downloads](https://poser.pugx.org/chekote/behat-retry-extension/downloads.svg)](https://packagist.org/packages/chekote/behat-retry-extension)
[![CircleCI](https://circleci.com/gh/Chekote/BehatRetryExtension.svg?style=shield)](https://circleci.com/gh/Chekote/BehatRetryExtension)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Chekote/BehatRetryExtension/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Chekote/BehatRetryExtension/?branch=master)
[![StyleCI](https://styleci.io/repos/110754153/shield?style=plastic)](https://styleci.io/repos/110754153)

# Behat Retry Extension
Automatically spin (retry) "Then" steps in Behat

## Usage

1. Add it to your requirements (typically dev only)

```bash
composer require --dev chekote/behat-retry-extension
```

2. Enable the extension:

```yaml
# behat.yml
default:
    # ...
    extensions:
        Chekote\BehatRetryExtension: ~
```

3. Optionally configure the extension

```yaml
# behat.yml
default:
    # ...
    extensions:
        Chekote\BehatRetryExtension:
          timeout: 10
          interval: 1000000000
```

## Configuration Options

### Timeout

Type: Float

Default: 5

The timeout setting is the number of seconds that the extension should retry "Then" steps until they are considered a failure.

### Interval

Type: Integer

Default: 100000000 (0.1 seconds)

The interval is how many nanoseconds the extension will wait between attempts. The default is to attempt 10 times a second. Attempting the retry more frequently will potentially allow your tests to pass quicker, but this depends on your environment.

It is possible that attempting the assertion too frequently will put a load on your application in such a way that the tests actually take longer to run. You will need to experiment with your particular application to determine what setting is best for you.

## Development

### pre-requisites

Install [Docker](https://www.docker.com).

You will also want to ensure that `./bin` is in your `$PATH` and is the highest priority. You can do so by adding the following to your shell profile:

```
export PATH=./bin:$PATH
```

### Setting up the project for development

Clone the repository:

```bash
git clone git@github.com:Chekote/BehatRetryExtension.git
cd BehatRetryExtension
```

Initialize the project:

```bash
init_project
```

### Tooling

The project includes a set of command line tools (such as php, etc) located in the bin folder. These can be run from anywhere on your machine and will execute as if they were the tools installed natively on your machine.

These commands will spin up temporary Docker containers to run your commands.

These command line tools have no requirements other than having Docker toolbox installed.

Note: If you are using the zsh terminal, you will need to unset the cdablevars option, otherwise you will be unable to execute any of the binaries that match usernames on your system, such as mysql:

.zshrc
```
# Options
unsetopt cdablevars
```
