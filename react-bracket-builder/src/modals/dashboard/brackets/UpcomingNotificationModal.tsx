import React from 'react'
import DisableUpcomingNotificationModal from './DisableUpcomingNotificationModal'
import EnableUpcomingNotificationModal from './EnableUpcomingNotificationModal'
import UpcomingNotificationRegisterModal from './UpcomingNotificationRegisterModal'

interface UpcomingNotificationModalProps {
  isUserLoggedIn: boolean
}

export const UpcomingNotificationModal = (
  props: UpcomingNotificationModalProps
) => {
  const { isUserLoggedIn } = props
  if (isUserLoggedIn) {
    return (
      <>
        <EnableUpcomingNotificationModal />
        <DisableUpcomingNotificationModal />
      </>
    )
  }
  return <UpcomingNotificationRegisterModal />
}
