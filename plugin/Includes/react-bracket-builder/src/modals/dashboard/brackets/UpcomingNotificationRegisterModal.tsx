import { ModalHeader } from '../../ModalHeader'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
import { useState } from 'react'
import { Modal } from '../../Modal'
import addClickHandlers from '../../addClickHandlers'

export default function UpcomingNotificationRegisterModal() {
  const [show, setShow] = useState(false)
  const [upcomingBracketId, setUpcomingBracketId] = useState<number>(null)
  addClickHandlers({
    buttonClassName: 'wpbb-enable-upcoming-notification-button',
    onButtonClick: (b) => {
      setShow(true)
      if (!b.dataset.bracketId) {
        throw new Error('Bracket id not found')
      }
      setUpcomingBracketId(parseInt(b.dataset.bracketId))
    },
  })
  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeader text={'Sign in or register to get notified'} />
      <div className="tw-flex tw-flex-col tw-gap-10">
        <ConfirmButton
          onClick={() => {
            document.cookie = `wpbb_upcoming_bracket_id=${upcomingBracketId}; path=/;`
            window.location.href = '/my-account'
          }}
        >
          <span>Sign in/register</span>
        </ConfirmButton>
        <CancelButton onClick={() => setShow(false)} />
      </div>
    </Modal>
  )
}
