import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:bmb_mobile/features/wp_http/wp_urls.dart';

class WebViewNavigationDelegate extends NavigationDelegate {
  final Function(bool) onLoadingChanged;
  final Function(String) onPageCompleted;
  final VoidCallback onLogout;

  // Paths that should open in external browser
  static const List<String> externalBrowserPaths = [
    '/shop/bmb/',
    '/referralprogram/',
    '/be-a-host/',
  ];

  // Domains that are allowed to load in WebView
  static const List<String> allowedExternalDomains = [
    'm.stripe.network',
    'js.stripe.com',
    'widgets.wp.com',
    'public-api.wordpress.com',
    'wordpress.com',
    'newassets.hcaptcha.com',
    'hcaptcha.com'
  ];

  WebViewNavigationDelegate({
    required this.onLoadingChanged,
    required this.onPageCompleted,
    required this.onLogout,
  }) : super(
          onProgress: (_) {},
          onPageStarted: (url) {
            AppLogger.debugLog('Page load started: $url');
            onLoadingChanged(true);
          },
          onPageFinished: (url) {
            AppLogger.debugLog('Page load finished: $url');
            onLoadingChanged(false);
            onPageCompleted(url);
          },
          onWebResourceError: (error) {
            AppLogger.debugLog(
              'Web resource error: ${error.description} (${error.errorCode})',
            );
            // Only update loading state if it's not a frame load interruption
            if (error.errorCode != 102) {
              onLoadingChanged(false);
            }
          },
          onNavigationRequest: (request) async {
            final uri = Uri.parse(request.url);

            // If it's our domain, check if it should open in external browser
            if (request.url.contains(WpUrls.baseUrl)) {
              // Check if URL contains any of the external browser paths
              final shouldOpenExternally = externalBrowserPaths.any(
                (path) => request.url.contains(path),
              );

              if (shouldOpenExternally) {
                AppLogger.debugLog(
                  'Opening in external browser: ${request.url}',
                );
                if (await canLaunchUrl(uri)) {
                  await launchUrl(uri, mode: LaunchMode.externalApplication);
                }
                return NavigationDecision.prevent;
              }

              // Check for login/unauthorized paths
              if (request.url.contains(WpUrls.loginPath) ||
                  request.url.contains('unauthorized') ||
                  uri.queryParameters['account_deleted'] == '1') {
                onLogout();
                return NavigationDecision.prevent;
              }
              AppLogger.debugLog('Navigating to internal URL: ${request.url}');
              return NavigationDecision.navigate;
            }

            // Check if this is a resource request (not a page navigation)
            final isResource = uri.path.toLowerCase().contains(RegExp(
                  r'\.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|map)$',
                ));

            final isAllowedDomain = allowedExternalDomains
                .any((domain) => request.url.contains(domain));

            // Allow resource requests to load in WebView
            if (isResource || isAllowedDomain) {
              AppLogger.debugLog('Loading resource: ${request.url}');
              return NavigationDecision.navigate;
            }

            // For all other external URLs, open in external browser
            if (await canLaunchUrl(uri)) {
              AppLogger.debugLog(
                'Launching external domain: ${request.url}',
              );
              await launchUrl(uri, mode: LaunchMode.externalApplication);
            }
            return NavigationDecision.prevent;
          },
        );
}
