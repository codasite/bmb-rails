import { ModalHeader } from '../../ModalHeader'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
import { useState } from 'react'
import { Modal } from '../../Modal'
import addClickHandlers from '../../addClickHandlers'
import { ReactComponent as LinkIcon } from '../../../brackets/shared/assets/link.svg'

export default function ShareTournamentModal() {
  const [show, setShow] = useState(false)
  const [playTournamentUrl, setPlayTournamentUrl] = useState('')
  addClickHandlers({
    buttonClassName: 'wpbb-share-tournament-button',
    onButtonClick: (b) => {
      setPlayTournamentUrl(b.dataset.playTournamentUrl)
      setShow(true)
    },
  })
  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeader text={'Share Tournament'} />
      <div className="tw-flex tw-flex-col tw-gap-10">
        <ConfirmButton
          disabled={false}
          onClick={() => {
            navigator.clipboard.writeText(playTournamentUrl).catch((err) => {
              console.error(err)
              console.error(
                'If "navigator.clipboard is undefined", you may not be using a secure origin.'
              )
            })
            setShow(false)
          }}
        >
          <LinkIcon />
          <span>Copy link</span>
        </ConfirmButton>
        <CancelButton onClick={() => setShow(false)} />
      </div>
    </Modal>
  )
}
