import { useState } from 'react'
import addClickHandlers from '../../addClickHandlers'
import { Modal } from '../../Modal'
import { ModalHeader } from '../../ModalHeader'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
import { bracketApi } from '../../../brackets/shared'

export const LockLiveTournamentModal = () => {
  const [show, setShow] = useState(false)
  const [bracketId, setBracketId] = useState<number | null>(null)
  const [bracketTitle, setBracketTitle] = useState<string | null>(null)

  addClickHandlers({
    buttonClassName: 'wpbb-lock-tournament-button',
    onButtonClick: (b) => {
      b.dataset.bracketId && setBracketId(parseInt(b.dataset.bracketId))
      b.dataset.bracketTitle && setBracketTitle(b.dataset.bracketTitle)
      setShow(true)
    },
  })
  const headerText = `Lock plays for ${
    bracketTitle ? `"${bracketTitle}"` : 'this tournament'
  }?`

  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeader text={headerText} />
      <p className="tw-text-center">
        No new plays can be made once a tournament is locked.
      </p>
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
          <span>Confirm</span>
        </ConfirmButton>
        <CancelButton onClick={() => setShow(false)}></CancelButton>
      </div>
    </Modal>
  )
}
