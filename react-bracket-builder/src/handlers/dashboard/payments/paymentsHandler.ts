import { bracketApi } from '../../../brackets/shared'
import * as Sentry from '@sentry/browser'

export function paymentsHandler() {
  const buttons = document.getElementsByClassName('wpbb-payments-button')
  for (const button of buttons) {
    button.addEventListener('click', async (e) => {
      const button = e.currentTarget as HTMLButtonElement
      button.disabled = true
      try {
        const { url } = await bracketApi.getStripePaymentsLink()
        window.location.href = url
      } catch (error) {
        Sentry.captureException(error)
        button.disabled = false
      }
    })
  }
}
