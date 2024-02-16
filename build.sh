#!/bin/sh

echo "{\"app\": \"moncycle.app\", \"version\": \"$(git describe --tags)\", \"build\": \"$(date +%Y-%m-%d)\"}" > www_data/api/version.json

docker build --platform linux/amd64 -t jeanio/moncycle.app .
