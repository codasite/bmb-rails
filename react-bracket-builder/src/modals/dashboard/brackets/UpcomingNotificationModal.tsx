// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { DisableUpcomingNotificationModal } from './DisableUpcomingNotificationModal'
import { EnableUpcomingNotificationModal } from './EnableUpcomingNotificationModal'
import UpcomingNotificationRegisterModal from './UpcomingNotificationRegisterModal'
import { BracketData } from './BracketData'

interface UpcomingNotificationModalProps {
  isUserLoggedIn: boolean
  loginUrl: string
  showEnable: boolean
  setShowEnable: (show: boolean) => void
  showDisable: boolean
  setShowDisable: (show: boolean) => void
  bracketData: BracketData
}

export const UpcomingNotificationModal = (
  props: UpcomingNotificationModalProps
) => {
  const {
    isUserLoggedIn,
    showEnable,
    setShowEnable,
    showDisable,
    setShowDisable,
    bracketData,
  } = props

  if (isUserLoggedIn) {
    return (
      <>
        <EnableUpcomingNotificationModal
          show={showEnable}
          setShow={setShowEnable}
          bracketData={bracketData}
        />
        <DisableUpcomingNotificationModal
          show={showDisable}
          setShow={setShowDisable}
          bracketData={bracketData}
        />
      </>
    )
  }
  return <UpcomingNotificationRegisterModal loginUrl={props.loginUrl} />
}
