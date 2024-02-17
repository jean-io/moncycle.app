#!/bin/sh

echo "{\"app\": \"moncycle.app\", \"version\": \"$(git describe --tags)\", \"build\": \"$(date +%Y-%m-%d)\", \"commit\": \"$(git rev-parse --short HEAD)\"}" > www_data/api/version.json

docker build --platform linux/amd64 -t jeanio/moncycle.app .
