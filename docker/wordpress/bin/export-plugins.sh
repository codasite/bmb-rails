#!/bin/bash

export_plugins() {
    # Allow overriding directories via environment variables
    SOURCE_DIR="${WP_PLUGINS_DIR:-/var/www/html/wp-content/plugins}"
    DEST_DIR="${EXPORT_DEST_DIR:-$HOME/plugins}"
    ZIP_FILE="${EXPORT_ZIP_FILE:-$HOME/plugins.zip}"

    # Create destination directory if it doesn't exist
    mkdir -p "$DEST_DIR"

    # Create a temporary file to store valid plugin names
    temp_file=$(mktemp)
    trap 'rm -f "$temp_file"' EXIT

    # If a file is provided as argument, use it, otherwise read from STDIN
    if [ $# -gt 0 ]; then
        input_file="$1"
        if [ ! -f "$input_file" ]; then
            echo "Error: $input_file not found"
            exit 1
        fi
        exec < "$input_file"
    else
        echo "Reading plugin list from STDIN..."
    fi

    # First pass: validate and store plugin names
    while IFS= read -r plugin || [ -n "$plugin" ]; do
        # Skip empty lines
        if [ -z "$plugin" ]; then
            continue
        fi
        
        # Remove any whitespace
        plugin=$(echo "$plugin" | tr -d '[:space:]')
        
        # Check if source plugin directory exists
        if [ -d "$SOURCE_DIR/$plugin" ]; then
            echo "$plugin" >> "$temp_file"
        else
            echo "Warning: Plugin directory '$plugin' not found in $SOURCE_DIR"
        fi
    done

    # Remove plugins not in the list
    echo "Checking for plugins to remove..."
    for plugin_dir in "$DEST_DIR"/*; do
        if [ -d "$plugin_dir" ]; then
            plugin_name=$(basename "$plugin_dir")
            if ! grep -Fxq "$plugin_name" "$temp_file"; then
                echo "Removing unlisted plugin: $plugin_name"
                rm -rf "$plugin_dir"
            fi
        fi
    done

    # Second pass: sync the plugins
    while IFS= read -r plugin; do
        echo "Syncing $plugin..."
        rsync -av --delete "$SOURCE_DIR/$plugin/" "$DEST_DIR/$plugin/"
    done < "$temp_file"

    # Create zip archive
    echo "Creating plugins archive..."
    rm -f "$ZIP_FILE"  # Remove existing zip if it exists
    cd "$(dirname "$DEST_DIR")" && zip -r "$ZIP_FILE" "$(basename "$DEST_DIR")"

    echo "Done! Plugins synced to $DEST_DIR and archived to $ZIP_FILE"
}

# If the script is being sourced, don't execute automatically
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    export_plugins "$@"
fi