# WHMCS BlueOnyx Server Module

This repository contains the BlueOnyx server module for WHMCS.

It provides the BlueOnyx hosting control integration, customer-side service
actions, a BlueOnyx login handoff, and the supporting templates and assets
needed to package the module as a standalone download.

BlueOnyx project information: https://www.blueonyx.it
BlueOnyx core repository: https://github.com/MichaelStauber/BlueOnyx

## What is in this repository

- the WHMCS server module under `modules/servers/blueonyx/`
- the module metadata file `modules/servers/blueonyx/whmcs.json`
- the release packager in `build.sh`
- the changelog and handover notes
- the repository license files

## Requirements

- WHMCS with server module support
- a working BlueOnyx APIv2 endpoint
- PHP with cURL enabled on the WHMCS host

## Installation

Copy the `modules/servers/blueonyx/` directory into the matching WHMCS
installation path, preserving the directory structure.

Typical target path:

`/path/to/whmcs/modules/servers/blueonyx/`

Then enable the module in WHMCS and configure the BlueOnyx server hostname,
access hash, and client secret.

## Package contents

The release archive built by `build.sh` is intended to contain the following:

- `README.md`
- `CHANGELOG.md`
- `HANDOVER.md`
- `LICENSE`
- `SUN-modified-BSD-License.txt`
- `VERSION`
- `build.sh`
- `docs/`
- `modules/servers/blueonyx/`

## Versioning

This repository uses a simple version file as the packaging source of truth.

- `VERSION` contains the release version
- `CHANGELOG.md` records user-facing changes
- Git tags should match the version string, for example `v3.0.0`

## Release artifact

The packaged download is built from the repository root and is named:

`whmcs-blueonyx-server-module-<version>.zip`

Run `./build.sh` to create the release archive locally.

## Support

- Issues and pull requests belong in this repository
- BlueOnyx core changes belong in the BlueOnyx repository
- License information is provided in `LICENSE`
