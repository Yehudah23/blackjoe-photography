#!/bin/bash
# Test file upload to portfolio API
# Usage: ./test-upload.sh <image-file>

if [ -z "$1" ]; then
    echo "Usage: $0 <image-file>"
    echo "Example: $0 test-image.jpg"
    exit 1
fi

FILE="$1"

if [ ! -f "$FILE" ]; then
    echo "Error: File '$FILE' not found"
    exit 1
fi

echo "Testing upload to http://localhost:8000/api/portfolio"
echo "File: $FILE"
echo ""

# First, login to get session cookie
echo "Step 1: Logging in..."
curl -c /tmp/cookies.txt \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"password":"admin123"}' \
  http://localhost:8000/api/admin/login

echo -e "\n\nStep 2: Uploading file..."
curl -b /tmp/cookies.txt \
  -X POST \
  -F "file=@$FILE" \
  -F "title=Test Upload" \
  -F "category=Test" \
  -F "description=Uploaded via test script" \
  http://localhost:8000/api/portfolio

echo -e "\n\nStep 3: Listing portfolio items..."
curl http://localhost:8000/api/portfolio | jq '.'

rm /tmp/cookies.txt
echo ""
echo "Test complete!"
