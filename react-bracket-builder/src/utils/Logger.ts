import * as Sentry from '@sentry/browser'
import { wpbbAjax } from './WpbbAjax'

class Logger {
  constructor() {
    this.initializeSentry()
  }

  getSentryOptions() {
    const appObj = wpbbAjax.getAppObj()
    return {
      dsn:
        appObj?.sentryDsn ||
        'https://2e4df9ae93914f279c4ab59721811edc@o4505256728330240.ingest.sentry.io/4505256731082752',
      environment: appObj?.sentryEnv || 'development',
    }
  }

  initializeSentry() {
    const { environment, dsn } = this.getSentryOptions()
    if (dsn) {
      Sentry.init({
        environment: environment || 'production',
        dsn: dsn,
        integrations: [
          new Sentry.BrowserTracing({
            // Set `tracePropagationTargets` to control for which URLs distributed tracing should be enabled
            tracePropagationTargets: [
              'localhost',
              /^https:\/\/backmybracket\.com/,
            ],
          }),
          new Sentry.Replay(),
        ],
        // Performance Monitoring
        tracesSampleRate: 0.1, // Capture 100% of the transactions, reduce in production!
        // Session Replay
        replaysSessionSampleRate: 0.1, // This sets the sample rate at 10%. You may want to change it to 100% while in development and then sample at a lower rate in production.
        replaysOnErrorSampleRate: 1.0, // If you're not already sampling the entire session, change the sample rate to 100% when sampling sessions where errors occur.
      })
    }
  }
  error(error: Error | string, extraData: any = {}) {
    console.error(error)
    Sentry.captureException(error, { extra: extraData })
  }
  log(message: string, extraData: any = {}) {
    Sentry.captureMessage(message, { extra: extraData })
  }
}

export const logger = new Logger()
