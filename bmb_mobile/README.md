# BMB Mobile

A Flutter mobile app for BackMyBracket.com.

## Getting Started

This project is a starting point for a Flutter application.

### Development Setup

A few resources to get you started if this is your first Flutter project:

- [Lab: Write your first Flutter app](https://docs.flutter.dev/get-started/codelab)
- [Cookbook: Useful Flutter samples](https://docs.flutter.dev/cookbook)
- [Online documentation](https://docs.flutter.dev/)

### Version Management

The app version follows the format `x.y.z+b` where:
- `x.y.z` is the semantic version (major.minor.patch)
- `b` is the build number

To manage versions:
```bash
task app:version:get        # Display current version
task app:version:bump-patch # Increment patch version (1.0.2 → 1.0.3)
task app:version:bump-build # Increment build number (1.0.2+1 → 1.0.2+2)
```

Before deploying a new version:
1. Decide if you need a patch version bump (for bug fixes) or build number bump (for internal testing)
2. Run the appropriate version bump command
3. Commit the version change
4. Build and deploy

### App Store Connect Setup

To deploy the app to the App Store, you'll need to set up your App Store Connect credentials:

1. Copy the example environment file:
   ```bash
   cp .env.appstoreconnect.example .env.appstoreconnect
   ```

2. Get your App Store Connect API credentials:
   - Go to [App Store Connect](https://appstoreconnect.apple.com)
   - Navigate to Users and Access > Keys
   - Create a new API Key with App Manager role
   - Download the API key file and note the Key ID
   - Copy the Issuer ID from the Keys page

3. Update `.env.appstoreconnect` with your credentials:
   ```
   API_KEY=your_api_key_here
   API_ISSUER=your_issuer_id_here
   ```

4. Test your credentials:
   ```bash
   task ios:test-credentials
   ```

5. To deploy to App Store Connect:
   ```bash
   task ios:build    # Build the IPA
   task ios:push     # Upload to App Store Connect
   ```

Note: The `.env.appstoreconnect` file is gitignored to keep credentials secure. Make sure to safely store your credentials outside of version control.
