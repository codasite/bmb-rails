import { ModalHeader } from '../../ModalHeader'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
import { useState } from 'react'
import { Modal } from '../../Modal'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import { BracketData } from './BracketData'

interface DisableUpcomingNotificationModalProps {
  show: boolean
  setShow: (show: boolean) => void
  bracketData: BracketData
}

export const DisableUpcomingNotificationModal = (
  props: DisableUpcomingNotificationModalProps
) => {
  const [loading, setLoading] = useState(false)

  const handleDisable = async () => {
    if (!props.bracketData.notificationId) return

    setLoading(true)
    try {
      await bracketApi.removeNotification(props.bracketData.notificationId)
      window.location.reload()
    } catch (error) {
      console.error('Error disabling notification:', error)
    } finally {
      setLoading(false)
    }
  }

  return (
    <Modal show={props.show} setShow={props.setShow}>
      <ModalHeader text={'Turn off\nNotifications?'} />
      <div className="tw-flex tw-flex-col tw-gap-10">
        <ConfirmButton color="white" disabled={loading} onClick={handleDisable}>
          <span>Confirm</span>
        </ConfirmButton>
        <CancelButton onClick={() => props.setShow(false)} />
      </div>
    </Modal>
  )
}
