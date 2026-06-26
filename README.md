# ten7/github-code-testing

A Composer plugin that scaffolds TEN7's GitHub Actions and workflow files into
Drupal projects.
Requires [lullabot/drainpipe](https://github.com/lullabot/drainpipe) as a
dependency — even on projects not using drainpipe workflows, as it provides the
build tooling the actions depend on.

## Installation

```bash
composer require ten7/code-testing
```

## Configuration

Add a `code_testing` key to the `extra` section of your project's
`composer.json`. Under `github`, declare which workflows to scaffold using a
context key and a list of workflow names. 

⚠️ Note that drainpipe is required for any code_testing drainpipe functions.

```json
{
  "extra": {
    "drainpipe": {
      "github": {
        "pantheon": [
          "Actions"
        ]
      }
    },
    "code_testing": {
      "github": {
        "drainpipe": [
          "PlaywrightTests",
          "BrowserStackTests",
          "PantheonBuildEdge",
          "PantheonBuildMain",
          "ReviewApps"
        ],
        "github": [
          "CodeTests"
        ],
        "composer": [
          "LockDiff"
        ]
      }
    }
  }
}
```

Each entry maps to a workflow file using the pattern `{context}{Name}.yml`. The
example above would scaffold:

- `drainpipePlaywrightTests.yml`
- `drainpipeBrowserStackTests.yml`
- `githubCodeTests.yml`
- `pantheonReviewApps.yml`

It's entirely possible to skip the extras:code_testing" section and just copy 
the desired files from ./vendor/ten7/scaffold/.github/workflows

## Workflow contexts

| Context      | Use when                                                     |
|--------------|--------------------------------------------------------------|
| `drainpipe`  | The project uses drainpipe for its build and deploy pipeline |
| `deployment` | The project uses a non-drainpipe deployment pipeline         |
| `pantheon`   | The project is hosted on Pantheon                            |
| `composer`   | Composer-related workflows (e.g., lock file diffs)           |
| `test`       | Standalone test utilities (e.g., Renovate)                   |

## Available workflows

| File                              | Context      | Name                |
|-----------------------------------|--------------|---------------------|
| `drainpipePlaywrightTests.yml`    | `drainpipe`  | `PlaywrightTests`   |
| `drainpipeBrowserStackTests.yml`  | `drainpipe`  | `BrowserStackTests` |
| `githubCodeTests.yml`             | `github`     | `CodeTests`         |
| `drainpipePantheonBuildMain.yml`  | `drainpipe`  | `PantheonBuildMain` |
| `drainpipePantheonBuildEdge.yml`  | `drainpipe`  | `PantheonBuildEdge` |
| `deploymentPlaywrightTests.yml`   | `deployment` | `PlaywrightTests`   |
| `deploymentBrowserStackTests.yml` | `deployment` | `BrowserStackTests` |
| `pantheonReviewApps.yml`          | `pantheon`   | `ReviewApps`        |
| `composerLockDiff.yml`            | `composer`   | `LockDiff`          |
| `testRenovate.yml`                | `test`       | `Renovate`          |

## Actions

All actions under `.github/actions/code-testing/` are scaffolded unconditionally. They have
no effect unless referenced by a workflow.

| Action                                          | Purpose                                             |
|-------------------------------------------------|-----------------------------------------------------|
| `actions/code-testing/browserstack/test`        | Run BrowserStack tests                              |
| `actions/code-testing/browserstack/validate`    | Validate BrowserStack configuration                 |
| `actions/code-testing/drupal/index-search`      | Trigger search index rebuild via Terminus           |
| `actions/code-testing/github/add-pr-url`        | Add the multidev URL to a pull request body         |
| `actions/code-testing/playwright/test`          | Run Playwright tests                                |
| `actions/code-testing/terminus/wait-for-build`  | Poll until a Pantheon multidev environment is ready |

## Using deployment-based GitHub actions

If your site uses a deployment (such as the Pantheon deployment system), the 
requirements are different. None of them require drainpipe.

```json
{
  "extra": {
    "code_testing": {
      "github": {
        "deployment": [
          "PlaywrightTests",
          "BrowserStackTests.yml"
        ],
        "github": [
          "CodeTests"
        ],
        "composer": [
          "LockDiff"
        ]
      }
    }
  }
}
```

Drainpipe scaffolds its own files first; this plugin runs afterward and
overwrites only the files it manages.

## Why workflows are opt-in

Workflows not declared in `code_testing` are never written to
`.github/workflows/`, so they never appear in the GitHub Actions tab. This keeps
Pantheon-specific or drainpipe-specific workflows out of projects that don't use
them.