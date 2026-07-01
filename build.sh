#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VERSION_FILE="${ROOT_DIR}/VERSION"
STAGING_DIR="$(mktemp -d)"

cleanup() {
  rm -rf "${STAGING_DIR}"
}
trap cleanup EXIT

if [[ ! -f "${VERSION_FILE}" ]]; then
  echo "Missing version file: ${VERSION_FILE}" >&2
  exit 1
fi

VERSION="$(tr -d '[:space:]' < "${VERSION_FILE}")"

if [[ -z "${VERSION}" ]]; then
  echo "VERSION file is empty" >&2
  exit 1
fi

if ! [[ "${VERSION}" =~ ^[0-9]+(\.[0-9]+){1,2}([.-][0-9A-Za-z]+)?$ ]]; then
  echo "VERSION does not look like a release version: ${VERSION}" >&2
  exit 1
fi

if ! command -v zip >/dev/null 2>&1; then
  echo "zip is required but was not found in PATH" >&2
  exit 1
fi

for required in \
  "${ROOT_DIR}/README.md" \
  "${ROOT_DIR}/CHANGELOG.md" \
  "${ROOT_DIR}/HANDOVER.md" \
  "${ROOT_DIR}/LICENSE" \
  "${ROOT_DIR}/SUN-modified-BSD-License.txt" \
  "${ROOT_DIR}/VERSION" \
  "${ROOT_DIR}/docs" \
  "${ROOT_DIR}/modules/servers/blueonyx/blueonyx.php" \
  "${ROOT_DIR}/modules/servers/blueonyx/CceApiClient.php" \
  "${ROOT_DIR}/modules/servers/blueonyx/autologin.php" \
  "${ROOT_DIR}/modules/servers/blueonyx/whmcs.json" \
  "${ROOT_DIR}/modules/servers/blueonyx/templates"
do
  if [[ ! -e "${required}" ]]; then
    echo "Missing required release asset: ${required}" >&2
    exit 1
  fi
done

ZIP_NAME="whmcs-blueonyx-server-module-${VERSION}.zip"
ZIP_PATH="${ROOT_DIR}/${ZIP_NAME}"

rm -f "${ZIP_PATH}"

mkdir -p "${STAGING_DIR}"
cp "${ROOT_DIR}/README.md" "${STAGING_DIR}/README.md"
cp "${ROOT_DIR}/CHANGELOG.md" "${STAGING_DIR}/CHANGELOG.md"
cp "${ROOT_DIR}/HANDOVER.md" "${STAGING_DIR}/HANDOVER.md"
cp "${ROOT_DIR}/LICENSE" "${STAGING_DIR}/LICENSE"
cp "${ROOT_DIR}/SUN-modified-BSD-License.txt" "${STAGING_DIR}/SUN-modified-BSD-License.txt"
cp "${ROOT_DIR}/VERSION" "${STAGING_DIR}/VERSION"
cp "${ROOT_DIR}/build.sh" "${STAGING_DIR}/build.sh"
cp -R "${ROOT_DIR}/docs" "${STAGING_DIR}/docs"
cp -R "${ROOT_DIR}/modules" "${STAGING_DIR}/modules"

cd "${STAGING_DIR}"
zip -r "${ZIP_PATH}" \
  README.md \
  CHANGELOG.md \
  HANDOVER.md \
  LICENSE \
  SUN-modified-BSD-License.txt \
  VERSION \
  build.sh \
  docs \
  modules/servers/blueonyx

echo "Created ${ZIP_PATH}"
