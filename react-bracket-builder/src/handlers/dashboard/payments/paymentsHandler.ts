import { bracketApi } from '../../../brackets/shared'

export function paymentsHandler() {
  const buttons = document.getElementsByClassName('wpbb-payments-button')
  for (const button of buttons) {
    button.addEventListener('click', async (e) => {
      const button = e.currentTarget as HTMLButtonElement
      button.disabled = true
      try {
        const { url } = await bracketApi.getStripeOnboardingLink()
        window.location.href = url
      } catch (error) {
        button.disabled = false
      }
    })
  }
}
