[![CircleCI](https://circleci.com/gh/Chekote/BehatRetryExtension.svg?style=shield)](https://circleci.com/gh/Chekote/BehatRetryExtension)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Chekote/BehatRetryExtension/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Chekote/BehatRetryExtension/?branch=master)
[![StyleCI](https://styleci.io/repos/110754153/shield?style=plastic)](https://styleci.io/repos/110754153)

# behat-then-spinner
Automatically spin (retry) "Then" steps in Behat

## Development pre-requisites

Install [Docker](https://www.docker.com).

You will also want to ensure that `./bin` is in your `$PATH` and is the highest priority. You can do so by adding the following to your shell profile:

```
export PATH=./bin:$PATH
```

## Setting up the project for development

Clone the repository:

```bash
git clone git@github.com:Chekote/behat-then-spinner.git
cd behat-then-spinner
```

Initialize the project:

```bash
init_project
```

## Tooling

The project includes a set of command line tools (such as php, etc) located in the bin folder. These can be run from anywhere on your machine and will execute as if they were the tools installed natively on your machine.

These commands will spin up temporary Docker containers to run your commands.

These command line tools have no requirements other than having Docker toolbox installed.

Note: If you are using the zsh terminal, you will need to unset the cdablevars option, otherwise you will be unable to execute any of the binaries that match usernames on your system, such as mysql:

.zshrc
```
# Options
unsetopt cdablevars
```
