# ten7/github-code-testing

A Composer plugin that scaffolds TEN7's GitHub Actions and workflow files into Drupal projects. Requires [lullabot/drainpipe](https://github.com/lullabot/drainpipe) as a dependency — even on projects not using drainpipe workflows, as it provides the build tooling the actions depend on.

## Installation

```bash
composer require ten7/github-code-testing
```

## Configuration

Add a `code_testing` key to the `extra` section of your project's `composer.json`. Under `github`, declare which workflows to scaffold using a context key and a list of workflow names.

```json
"extra": {
    "code_testing": {
        "github": {
            "drainpipe": ["PlaywrightTests", "BrowserStackTests", "GithubCodeTests"]
        }
    },
    "drainpipe": {
        "github": {
            "pantheon": ["ReviewApps"]
        }
    }
}
```

Each entry maps to a workflow file using the pattern `{context}{Name}.yml`. The example above would scaffold:

- `drainpipePlaywrightTests.yml`
- `drainpipeBrowserStackTests.yml`
- `drainpipeGithubCodeTests.yml`
- `pantheonReviewApps.yml`

## Workflow contexts

| Context | Use when |
|---|---|
| `drainpipe` | The project uses drainpipe for its build and deploy pipeline |
| `deployment` | The project uses a non-drainpipe deployment pipeline |
| `pantheon` | The project is hosted on Pantheon |
| `composer` | Composer-related workflows (e.g., lock file diffs) |
| `test` | Standalone test utilities (e.g., Renovate) |

## Available workflows

| File | Context | Name |
|---|---|---|
| `drainpipePlaywrightTests.yml` | `drainpipe` | `PlaywrightTests` |
| `drainpipeBrowserStackTests.yml` | `drainpipe` | `BrowserStackTests` |
| `drainpipeGithubCodeTests.yml` | `drainpipe` | `GithubCodeTests` |
| `drainpipePantheonBuildMain.yml` | `drainpipe` | `PantheonBuildMain` |
| `drainpipePantheonBuildEdge.yml` | `drainpipe` | `PantheonBuildEdge` |
| `deploymentPlaywrightTests.yml` | `deployment` | `PlaywrightTests` |
| `deploymentBrowserStackTests.yml` | `deployment` | `BrowserStackTests` |
| `pantheonReviewApps.yml` | `pantheon` | `ReviewApps` |
| `composerLockDiff.yml` | `composer` | `LockDiff` |
| `testRenovate.yml` | `test` | `Renovate` |

## Actions

All actions under `.github/actions/` are scaffolded unconditionally. They have no effect unless referenced by a workflow.

| Action | Purpose |
|---|---|
| `actions/browserstack/test` | Run BrowserStack tests |
| `actions/browserstack/validate` | Validate BrowserStack configuration |
| `actions/drupal/index-search` | Trigger search index rebuild via Terminus |
| `actions/github/add-pr-url` | Add the multidev URL to a pull request body |
| `actions/playwright/test` | Run Playwright tests |
| `actions/terminus/wait-for-build` | Poll until a Pantheon multidev environment is ready |

## Relationship to drainpipe

This package and drainpipe are configured independently. If a project uses both, declare each in its own `extra` block:

```json
"extra": {
    "drainpipe": {
        "github": {
            "pantheon": ["ReviewApps"]
        }
    },
    "code_testing": {
        "github": {
            "drainpipe": ["PlaywrightTests"]
        }
    }
}
```

Drainpipe scaffolds its own files first; this plugin runs afterward and overwrites only the files it manages.

## Why workflows are opt-in

Workflows not declared in `code_testing` are never written to `.github/workflows/`, so they never appear in the GitHub Actions tab. This keeps Pantheon-specific or drainpipe-specific workflows out of projects that don't use them.