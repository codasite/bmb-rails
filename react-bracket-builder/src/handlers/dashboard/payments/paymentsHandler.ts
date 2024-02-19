import { bracketApi } from '../../../brackets/shared/api/bracketApi'

export function paymentsHandler() {
  const buttons = document.getElementsByClassName('wpbb-payments-button')
  for (const button of buttons) {
    button.addEventListener('click', (e) => {
      const el = e.currentTarget as HTMLButtonElement
      const bracketId = parseInt(el.dataset.bracketId)
      if (!bracketId) {
        return
      }
      bracketApi
        .updateBracket(bracketId, { status: 'private' })
        .then((res) => {
          window.location.reload()
        })
        .catch((err) => {
          console.error(err)
        })
    })
  }
}
