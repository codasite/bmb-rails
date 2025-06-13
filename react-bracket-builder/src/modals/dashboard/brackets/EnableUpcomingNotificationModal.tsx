import { ModalHeader } from '../../ModalHeader'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
import { useState } from 'react'
import { Modal } from '../../Modal'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import { NotificationType } from '../../../brackets/shared/api/types/notification'
import { BracketData } from './BracketData'

interface EnableUpcomingNotificationModalProps {
  show: boolean
  setShow: (show: boolean) => void
  bracketData: BracketData
}

export const EnableUpcomingNotificationModal = (
  props: EnableUpcomingNotificationModalProps
) => {
  const [loading, setLoading] = useState(false)

  const handleEnable = async () => {
    if (!props.bracketData.id) return

    setLoading(true)
    try {
      await bracketApi.createNotification({
        notificationType: NotificationType.BracketUpcoming,
        postId: props.bracketData.id,
      })
      window.location.reload()
    } catch (error) {
      console.error('Error enabling notification:', error)
    } finally {
      setLoading(false)
    }
  }

  return (
    <Modal show={props.show} setShow={props.setShow}>
      <ModalHeader text={'Turn on\n Notifications?'} />
      <div className="tw-flex tw-flex-col tw-gap-10">
        <ConfirmButton disabled={loading} onClick={handleEnable}>
          <span>Confirm</span>
        </ConfirmButton>
        <CancelButton onClick={() => props.setShow(false)} />
      </div>
    </Modal>
  )
}
