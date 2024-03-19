import { useState } from 'react'
import addClickHandlers from '../../addClickHandlers'
import { Modal } from '../../Modal'
import { ModalHeader } from '../../ModalHeader'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
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

  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeader text="Lock this tournament to further entries?" />
      <div className="tw-flex tw-flex-col tw-gap-10">
        <ConfirmButton
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
          <span>Lock</span>
        </ConfirmButton>
        <CancelButton onClick={() => setShow(false)}></CancelButton>
      </div>
    </Modal>
  )
}
