#!/bin/bash

# URL of main Sitemap
echo "Sitemap URL: $1"

# Check the last execution timestamp for this domain
last_execution=$(cat /var/www/cache-warmup.eu/htdocs/external/last_execution.txt | grep "$1" | cut -d" " -f2)

# Get the current date and time
current_date=$(date +"%Y-%m-%d")

# Check if the last execution was more than a day ago
if [[ "$last_execution" != "$current_date" ]]; then

    # Extract all Sitemap URLs
    sitemap_urls=$(curl -s "$1" | grep -oP '(?<=<loc>)[^<]+')

    # Loop over and retrieve the individual URLs
    for sitemap in $sitemap_urls; do
        urls=$(curl -s "$sitemap" | grep -oP '(?<=<loc>)[^<]+')
        for url in $urls; do
            curl -IL "$url"
        done
    done

    # Update the last execution timestamp for this domain
    sed -i "s|$1.*|$1 $current_date|g" /var/www/cache-warmup.eu/htdocs/external/last_execution.txt

else
    echo "Cache WarmUP for this Domain has already been executed today. Come Back Tomorrow."
fi
