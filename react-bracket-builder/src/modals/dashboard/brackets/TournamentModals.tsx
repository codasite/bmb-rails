import { useEffect, useState } from 'react'
import { WpbbAppObj } from '../../../utils/WpbbAjax'
import { CompleteRoundModal } from './CompleteRoundModal'
import { DeleteBracketModal } from './DeleteBracketModal'
import { EditBracketModal } from './EditBracketModal'
import { LockLiveTournamentModal } from './LockLiveTournamentModal'
import { PublishBracketModal } from './PublishBracketModal'
import { SetTournamentFeeModal } from './SetTournamentFeeModal'
import { ShareBracketModal } from './ShareBracketModal'
import { UpcomingNotificationModal } from './UpcomingNotificationModal'
import { MoreOptionsModal } from './MoreOptionsModal'
import { BracketData } from './BracketData'

export const TournamentModalsContainer = (props: { appObj: WpbbAppObj }) => {
  const [showEditBracketModal, setShowEditBracketModal] = useState(false)
  const [showShareBracketModal, setShowShareBracketModal] = useState(false)
  const [showDeleteBracketModal, setShowDeleteBracketModal] = useState(false)
  const [showSetTournamentFeeModal, setShowSetTournamentFeeModal] =
    useState(false)
  const [showLockLiveTournamentModal, setShowLockLiveTournamentModal] =
    useState(false)
  const [showMoreOptionsModal, setShowMoreOptionsModal] = useState(false)
  return (
    <TournamentModals
      appObj={props.appObj}
      showEditBracketModal={showEditBracketModal}
      setShowEditBracketModal={setShowEditBracketModal}
      showSetTournamentFeeModal={showSetTournamentFeeModal}
      setShowSetTournamentFeeModal={setShowSetTournamentFeeModal}
      showShareBracketModal={showShareBracketModal}
      setShowShareBracketModal={setShowShareBracketModal}
      showLockLiveTournamentModal={showLockLiveTournamentModal}
      setShowLockLiveTournamentModal={setShowLockLiveTournamentModal}
      showDeleteBracketModal={showDeleteBracketModal}
      setShowDeleteBracketModal={setShowDeleteBracketModal}
      showMoreOptionsModal={showMoreOptionsModal}
      setShowMoreOptionsModal={setShowMoreOptionsModal}
    />
  )
}

export const TournamentModals = (props: {
  appObj: WpbbAppObj
  showEditBracketModal: boolean
  setShowEditBracketModal: (show: boolean) => void
  showSetTournamentFeeModal: boolean
  setShowSetTournamentFeeModal: (show: boolean) => void
  showShareBracketModal: boolean
  setShowShareBracketModal: (show: boolean) => void
  showLockLiveTournamentModal: boolean
  setShowLockLiveTournamentModal: (show: boolean) => void
  showDeleteBracketModal: boolean
  setShowDeleteBracketModal: (show: boolean) => void
  showMoreOptionsModal: boolean
  setShowMoreOptionsModal: (show: boolean) => void
}) => {
  const [bracketData, setBracketData] = useState<BracketData>({})
  useEffect(() => {
    console.log('bracketData', bracketData)
  }, [bracketData])

  return (
    <>
      <EditBracketModal
        show={props.showEditBracketModal}
        setShow={props.setShowEditBracketModal}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <SetTournamentFeeModal
        applicationFeeMinimum={props.appObj.applicationFeeMinimum}
        applicationFeePercentage={props.appObj.applicationFeePercentage}
        show={props.showSetTournamentFeeModal}
        setShow={props.setShowSetTournamentFeeModal}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <ShareBracketModal
        show={props.showShareBracketModal}
        setShow={props.setShowShareBracketModal}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <LockLiveTournamentModal
        show={props.showLockLiveTournamentModal}
        setShow={props.setShowLockLiveTournamentModal}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <DeleteBracketModal
        show={props.showDeleteBracketModal}
        setShow={props.setShowDeleteBracketModal}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <PublishBracketModal
        upgradeAccountUrl={props.appObj.upgradeAccountUrl}
        canCreateBracket={props.appObj.userCanShareBracket}
      />
      <UpcomingNotificationModal
        isUserLoggedIn={props.appObj.isUserLoggedIn}
        loginUrl={props.appObj.loginUrl}
      />
      <CompleteRoundModal />
      <MoreOptionsModal
        show={props.showMoreOptionsModal}
        setShow={props.setShowMoreOptionsModal}
        setShowDeleteBracketModal={props.setShowDeleteBracketModal}
        setShowEditBracketModal={props.setShowEditBracketModal}
        setShowLockLiveTournamentModal={props.setShowLockLiveTournamentModal}
        setShowSetTournamentFeeModal={props.setShowSetTournamentFeeModal}
        setShowShareBracketModal={props.setShowShareBracketModal}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
    </>
  )
}
