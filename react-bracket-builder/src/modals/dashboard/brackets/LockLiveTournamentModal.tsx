import { useState } from 'react'
import addClickHandlers from '../../addClickHandlers'
import { Modal } from '../../Modal'
import { ModalHeader } from '../../ModalHeader'
import { CancelButton, ConfirmButton, DangerButton } from '../../ModalButtons'
import { bracketApi } from '../../../brackets/shared'

export const LockLiveTournamentModal = () => {
  const [show, setShow] = useState(false)
  const [bracketId, setBracketId] = useState<number | null>(null)

  addClickHandlers({
    buttonClassName: 'wpbb-lock-tournament-button',
    onButtonClick: (b) => {
      b.dataset.bracketId && setBracketId(parseInt(b.dataset.bracketId))
      setShow(true)
    },
  })
  const headerText = `Update tournament status to "In Progress"?`

  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeader text={headerText} />
      <p className="tw-text-center">
        No new plays will be accepted into the tournament.
        <span className="tw-text-red"> This action cannot be undone.</span>
      </p>
      <div className="tw-flex tw-flex-col tw-gap-10">
        <DangerButton
          onClick={() => {
            // Lock the tournament
            if (!bracketId) {
              return
            }
            bracketApi
              .updateBracket(bracketId, { status: 'score' })
              .then(() => {
                window.location.reload()
              })
              .catch((err) => {
                console.error(err)
              })
          }}
        >
          <span>Confirm</span>
        </DangerButton>
        <CancelButton onClick={() => setShow(false)}></CancelButton>
      </div>
    </Modal>
  )
}
