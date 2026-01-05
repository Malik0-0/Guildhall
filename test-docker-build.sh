#!/bin/bash

# Test Docker build locally
echo "🐳 Testing Docker build locally..."
echo ""

# Build the Docker image
echo "Step 1: Building Docker image..."
docker build -t guildhall:test .

if [ $? -eq 0 ]; then
    echo "✅ Docker build successful!"
    echo ""
    echo "Step 2: Testing with docker-compose..."
    echo "Starting services..."
    docker-compose up -d
    
    echo ""
    echo "✅ Services started!"
    echo ""
    echo "You can now:"
    echo "  - Visit http://localhost:8000"
    echo "  - Check logs: docker-compose logs -f app"
    echo "  - Stop services: docker-compose down"
else
    echo "❌ Docker build failed!"
    echo "Check the error messages above."
    exit 1
fi

