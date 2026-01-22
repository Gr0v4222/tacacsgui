#!/bin/bash

# Build script for TACACSGUI Frontend
# This script builds the Angular application and deploys it to the web directory

set -e

echo "Building TACACSGUI Frontend..."

# Navigate to source directory
cd "$(dirname "$0")/web-src"

# Install dependencies if needed
if [ ! -d "node_modules" ]; then
    echo "Installing dependencies..."
    npm install
fi

# Build the application
echo "Building application..."
npm run build

# Navigate back to root
cd ..

# Backup old web files (excluding api and assets)
echo "Backing up old files..."
mkdir -p web-backup-$(date +%Y%m%d-%H%M%S)
BACKUP_DIR="web-backup-$(date +%Y%m%d-%H%M%S)"
find web -maxdepth 1 -type f \( -name "*.js" -o -name "*.css" -o -name "*.html" \) -exec cp {} "$BACKUP_DIR/" \; 2>/dev/null || true

# Remove old JS and CSS files from web directory (keep api and assets)
echo "Removing old compiled files..."
find web -maxdepth 1 -type f \( -name "*.js" -o -name "*.css" \) -delete 2>/dev/null || true

# Copy new build files to web directory
echo "Deploying new build..."
cp web-build/browser/* web/ 2>/dev/null || true

# Keep the original index.html if specific configuration is needed
# Uncomment the next line to restore original index.html structure
# cp web-backup-*/index.html web/index.html 2>/dev/null || true

echo "Build completed successfully!"
echo "New files deployed to web/ directory"
echo "Backup saved to $BACKUP_DIR/"
