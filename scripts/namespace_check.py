#!/usr/bin/env python3

import os
import sys
import re
import argparse
from typing import Tuple, Optional


def extract_namespace(content: str) -> Optional[str]:
    """Extract namespace from PHP file content."""
    namespace_pattern = r"namespace\s+([^;]+)"
    match = re.search(namespace_pattern, content)
    return match.group(1) if match else None


def path_to_namespace(file_path: str, base_dir: str, prefix: str) -> str:
    """Convert file path to expected namespace."""
    # Remove base directory and get parent directory path
    relative_path = os.path.relpath(os.path.dirname(file_path), base_dir)
    # Convert path separators to namespace separators
    namespace = relative_path.replace(os.sep, "\\")

    # Add prefix if provided
    return f"{prefix}\\{namespace}" if prefix else namespace


def check_file(file_path: str, base_dir: str, prefix: str) -> Tuple[bool, str]:
    """Check if file's namespace matches its path."""
    try:
        with open(file_path, "r", encoding="utf-8") as f:
            content = f.read()

        actual_namespace = extract_namespace(content)
        if not actual_namespace:
            return True, f"No namespace declaration found in {file_path}"

        expected_namespace = path_to_namespace(file_path, base_dir, prefix)

        # Handle empty namespace (files in root directory)
        if not expected_namespace and prefix:
            expected_namespace = prefix

        if actual_namespace.strip() != expected_namespace:
            return (
                False,
                f"Namespace mismatch in {file_path}:\n"
                f"  Expected: {expected_namespace}\n"
                f"  Found:    {actual_namespace}",
            )

        return True, f"Namespace correct in {file_path}"

    except Exception as e:
        return False, f"Error processing {file_path}: {str(e)}"


def main():
    parser = argparse.ArgumentParser(
        description="Check PHP namespace declarations match file paths"
    )
    parser.add_argument(
        "directories", nargs="+", help="One or more directories to check PHP files in"
    )
    parser.add_argument(
        "--base-dir",
        help="Base directory for namespace calculations (defaults to directory being checked)",
    )
    parser.add_argument(
        "--namespace-prefix", help="Namespace prefix to prepend", default=""
    )
    args = parser.parse_args()

    has_errors = False
    checked_count = 0
    error_count = 0

    for directory in args.directories:
        directory_path = os.path.abspath(directory)
        if not os.path.isdir(directory_path):
            print(f"Error: {directory_path} is not a directory")
            continue

        # Use base_dir if provided, otherwise use the directory being checked
        base_dir = os.path.abspath(args.base_dir) if args.base_dir else directory_path

        for root, _, files in os.walk(directory_path):
            for file in files:
                if not file.endswith(".php"):
                    continue

                file_path = os.path.join(root, file)
                success, message = check_file(
                    file_path, base_dir, args.namespace_prefix
                )

                if not success:
                    has_errors = True
                    error_count += 1
                    print(message)

                checked_count += 1

    print(f"Checked {checked_count} files, found {error_count} errors")
    sys.exit(1 if has_errors else 0)


if __name__ == "__main__":
    main()
