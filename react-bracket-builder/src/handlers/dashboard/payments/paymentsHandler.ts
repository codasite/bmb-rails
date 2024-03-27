import { bracketApi } from '../../../brackets/shared'
import { logger } from '../../../utils/Logger'

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
        logger.error(error)
        button.disabled = false
      }
    })
  }
}
