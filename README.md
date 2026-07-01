# ten7/github-code-testing

A Composer plugin that scaffolds TEN7's GitHub Actions and workflow files into
Drupal projects.
Requires [lullabot/drainpipe](https://github.com/lullabot/drainpipe) as a
dependency — even on projects not using drainpipe workflows, as it provides the
build tooling the actions depend on.

## Installation

```bash
composer require "ten7/github-code-testing"
```

⚠️If you have lullabot/drainpipe and/or lullabot/drainpipe-dev you will need
to uninstall them before installing github-code-testing.

## Configuration

Add a `code_testing` key to the `extra` section of your project's
`composer.json`. Under `github`, declare which workflows to scaffold using a
context key and a list of workflow names.

#### Required Secrets, Variables and PR Labels

##### Secrets

* `SSH_PRIVATE_KEY` is needed for all actions.
* `SSH_KNOWN_HOSTS` is also needed for all actions.
* `TERMINUS_MACHINE_TOKEN` is required for all actions
* `PANTHEON_REVIEW_RUN_INSTALLER` is required for drainpipe actions
* `PANTHEON_SITE_NAME` is the required by drainpipe actions. It is the UUID 
  for the site. It's annoying to have this in both variables and secrets. It 
  really should just be a variable unless at some point Pantheon allows 
  renaming a site and the UUID serves it's real purpose of being the 
  constant identifier. 
* `BROWSERSTACK_USERNAME` is the short key indicating the username (should be 
  the company-wide/repo-holder's username at browserstack.com)
* `BROWSERSTACK_ACCESS_KEY` is the hash at the same account at browserstack.com

##### Variables

* `BROWSERSTACK_PRIMARY_BRANCH` is the branch (edge, main, master) that 
  browserstack
* tests should be run on.
* `BROWSERSTACK_TESTS_ENABLED` is a boolean that indicates if browserstack
  tests should be run at all.
* `TESTING_NEEDS_SEARCH_INDEXING` is a boolean that determines if tests run on 
  the site require a build to run the search_api index.
* `PANTHEON_SITE_NAME` is the human-friendly site name that is used in 
  multi-dev URLs: https:/dev-[site-name].pantheonsite.io

##### PR Labels

On PRs some labels can be used for triggering actions these include

* `skip-wipe` which prevents a build from wiping the files and db and starting 
  from scratch on every push
* `build multidev` which is required to actually run drainpipePantheonReviewApps
* `test renovate` is useful if you want to reduce testRenovate's time to run 
  even more. See below.
* `browserstack` By default browserstack tests are never run on PRs even if 
  `BROWSERSTACK_TESTS_ENABLED`=true. Add this label if a PR should also run 
  browserstack tests. This is useful if a PR includes significant 
  theme changes. Less "expensive" standard CodeTests or Playwright tests. So 
  use this with care.

### Drainpipe Example

This automatically loads all the lullabot/drainpipe actions that are needed
for the specific tasks. There is no need to call drainpipe separately unless
you have specific additional requirements for those. Just be sure not to
overlap functionality.

```json
{
  "code_testing": {
    "github": {
      "drainpipe": [
        "PlaywrightTests",
        "BrowserStackTests",
        "PantheonBuildEdge",
        "PantheonBuildMain",
        "PantheonReviewApps",
        "CodeTests",
        "LockDiff"
      ]
    }
  }
}
```

#### testRenovate

This is required by drainpipe, but may not actually get installed. If you 
want to use this and you have instructions in a renovate.json and have it 
configured on Github, you need to add the following to `extra.code_testing`.

```json
{
    "test": [
      "Renovate",
    ]
}
```

### Deployment Example

For sites that use a deployment solution such as pantheon's github builder,
the options are simpler.

```json
{
  "code_testing": {
    "github": {
      "deployment": [
        "PlaywrightTests",
        "BrowserStackTests",
        "CodeTests",
        "LockDiff"
      ]
    }
  }
}
```

### Workflow file patterns

Each entry maps to a workflow file using the pattern `{context}{Name}.yml`. The
example above would scaffold:

- `drainpipePlaywrightTests.yml`
- `deploymentBrowserStackTests.yml`
- `deploymentCodeTests.yml`
- `testRenovate.yml`

It's entirely possible to skip the extras:code_testing" section and just copy
the desired files from ./vendor/ten7/scaffold/.github/workflows, but this
should not be necessary. If there need to be new changes, they should be
made here with broadly abstracted steps and making use of updates users can
make in their repos (secrets/variables/labels) or with specifically required
scripts test/playwright/package.json

## Workflow contexts

| Context      | Use when                                                     |
|--------------|--------------------------------------------------------------|
| `drainpipe`  | The project uses drainpipe for its build and deploy pipeline |
| `deployment` | The project uses a non-drainpipe deployment pipeline         |
| `test`       | Standalone test utilities (e.g., Renovate)                   |

## Available workflows

See the directory scaffold/.github/workflows

## Actions

All actions under `.github/actions/code-testing/` are scaffolded
unconditionally. They have no effect unless referenced by a workflow. Each 
action has a description defining what it does

This plugin also installs certain lullabot/drainpipe actions as needed.

## Why workflows are opt-in

Workflows not declared in `code_testing` are never written to
`.github/workflows/`, so they never appear in the GitHub Actions tab. This keeps
Pantheon-specific or drainpipe-specific workflows out of projects that don't use
them.

## How to use with Playwright and BrowserStack testing.

This project expects tests to be in a directory `./test/playwright`. In the
package.json file you will need to create scripts that you want to run as
follows:

* test:ci-ddev
* test:ci-pantheon
* test:browserstack

These tests should define what should be run by npm. An example might be:

```json
{
  "test:ci-ddev": "playwright test --project=func-chromium",
  "test:ci-pantheon": "playwright test --grep='@smoke' --project=func-chromium",
  "test:browserstack": "BROWSERSTACK_BUILD=true browserstack-node-sdk playwright test --grep='@browserstack'"
}

```

The test:browserstack should be configured differently based on the rules for
developing with BrowserStack that are beyond the scope of this repo. 