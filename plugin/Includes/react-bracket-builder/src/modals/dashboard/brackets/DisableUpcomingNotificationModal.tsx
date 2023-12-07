import { ModalHeader } from '../../ModalHeader'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
import { useState } from 'react'
import { Modal } from '../../Modal'
import addClickHandlers from '../../addClickHandlers'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'

export default function DisableUpcomingNotificationModal() {
  const [show, setShow] = useState(false)
  const [notificationId, setNotificationId] = useState<number>(null)
  addClickHandlers({
    buttonClassName: 'wpbb-disable-upcoming-notification-button',
    onButtonClick: (b) => {
      setNotificationId(parseInt(b.dataset.notificationId))
      setShow(true)
    },
  })
  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeader text={'Turn off\nNotifications?'} />
      <div className="tw-flex tw-flex-col tw-gap-10">
        <ConfirmButton
          variant="white"
          backgroundColor="white/15"
          onClick={() => {
            bracketApi
              .removeNotification(notificationId)
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
