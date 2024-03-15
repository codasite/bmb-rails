import React from 'react'
import DisableUpcomingNotificationModal from './DisableUpcomingNotificationModal'
import EnableUpcomingNotificationModal from './EnableUpcomingNotificationModal'
import UpcomingNotificationRegisterModal from './UpcomingNotificationRegisterModal'

export const UpcomingNotificationModal = (props: {
  isUserLoggedIn: boolean
  loginUrl: string
}) => {
  const { isUserLoggedIn } = props
  if (isUserLoggedIn) {
    return (
      <>
        <EnableUpcomingNotificationModal />
        <DisableUpcomingNotificationModal />
      </>
    )
  }
  return <UpcomingNotificationRegisterModal loginUrl={props.loginUrl} />
}
