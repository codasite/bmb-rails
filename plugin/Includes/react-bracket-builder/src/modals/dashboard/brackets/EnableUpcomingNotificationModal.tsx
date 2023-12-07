import { ModalHeader } from '../../ModalHeader'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
import { useState } from 'react'
import { Modal } from '../../Modal'
import addClickHandlers from '../../addClickHandlers'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import { NotificationType } from '../../../brackets/shared/api/types/notification'

export default function EnableUpcomingNotificationModal() {
  const [show, setShow] = useState(false)
  const [bracketId, setBracketId] = useState<number>(null)
  addClickHandlers({
    buttonClassName: 'wpbb-enable-upcoming-notification-button',
    onButtonClick: (b) => {
      setBracketId(parseInt(b.dataset.bracketId))
      setShow(true)
    },
  })
  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeader text={'Turn on\n Notifications?'} />
      <div className="tw-flex tw-flex-col tw-gap-10">
        <ConfirmButton
          onClick={() => {
            bracketApi
              .createNotification({
                notificationType: NotificationType.BracketUpcoming,
                postId: bracketId,
              })
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
        <CancelButton onClick={() => setShow(false)} />
      </div>
    </Modal>
  )
}
