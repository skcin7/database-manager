#!/bin/bash

# Simple script that can be used to deploy the package.

# Set default commit message.
MESSAGE="Deploy script used at `date '+%d/%m/%Y_%H:%M:%S'`."

# Add all files of current saved version and commit with commit message.
git add -A .
git commit -m "${MESSAGE}"

# Push to server:
git push