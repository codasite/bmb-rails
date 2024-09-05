import { useState } from 'react'
import { wpbbAjax, WpbbAppObj } from '../../../utils/WpbbAjax'
import { CompleteRoundModal } from './CompleteRoundModal'
import { DeleteBracketModal } from './DeleteBracketModal'
import { EditBracketModal } from './EditBracketModal'
import { LockLiveTournamentModal } from './LockLiveTournamentModal'
import { PublishBracketModal } from './PublishBracketModal'
import { SetTournamentFeeModal } from './SetTournamentFeeModal'
import { ShareBracketModal } from './ShareBracketModal'
import { UpcomingNotificationModal } from './UpcomingNotificationModal'
import addClickHandlers from '../../addClickHandlers'
import { MoreOptionsModal } from './MoreOptionsModal'

export const TournamentModals = (props: { appObj: WpbbAppObj }) => {
  const [showEditBracketModal, setShowEditBracketModal] = useState(false)
  const [showShareBracketModal, setShowShareBracketModal] = useState(false)
  const [showDeleteBracketModal, setShowDeleteBracketModal] = useState(false)
  const [showPublishBracketModal, setShowPublishBracketModal] = useState(false)
  const [showUpcomingNotificationModal, setShowUpcomingNotificationModal] =
    useState(false)
  const [showSetTournamentFeeModal, setShowSetTournamentFeeModal] =
    useState(false)
  const [showLockLiveTournamentModal, setShowLockLiveTournamentModal] =
    useState(false)
  const [showCompleteRoundModal, setShowCompleteRoundModal] = useState(false)
  const [showMoreOptionsModal, setShowMoreOptionsModal] = useState(true)

  const [bracketId, setBracketId] = useState<number | null>(null)
  const [bracketTitle, setBracketTitle] = useState('')
  const [bracketMonth, setBracketMonth] = useState('')
  const [bracketYear, setBracketYear] = useState('')
  const [bracketFee, setBracketFee] = useState<number>(null)
  const [playBracketUrl, setPlayBracketUrl] = useState('')
  const [copyBracketUrl, setCopyBracketUrl] = useState('')
  const [mostPopularPicksUrl, setMostPopularPicksUrl] = useState('')

  const resetState = () => {
    setBracketId(null)
    setBracketTitle('')
    setBracketMonth('')
    setBracketYear('')
    setBracketFee(null)
    setPlayBracketUrl('')
    setCopyBracketUrl('')
    setMostPopularPicksUrl('')
    setShowEditBracketModal(false)
  }
  return (
    <>
      <EditBracketModal
        show={showEditBracketModal}
        setShow={setShowEditBracketModal}
        resetState={resetState}
        bracketId={bracketId}
        bracketTitle={bracketTitle}
        setBracketTitle={setBracketTitle}
        bracketMonth={bracketMonth}
        setBracketMonth={setBracketMonth}
        bracketYear={bracketYear}
        setBracketYear={setBracketYear}
        setBracketId={setBracketId}
      />
      <ShareBracketModal />
      <DeleteBracketModal />
      <PublishBracketModal
        upgradeAccountUrl={props.appObj.upgradeAccountUrl}
        canCreateBracket={props.appObj.userCanShareBracket}
      />
      <UpcomingNotificationModal
        isUserLoggedIn={props.appObj.isUserLoggedIn}
        loginUrl={props.appObj.loginUrl}
      />
      <SetTournamentFeeModal
        applicationFeeMinimum={props.appObj.applicationFeeMinimum}
        applicationFeePercentage={props.appObj.applicationFeePercentage}
      />
      <LockLiveTournamentModal />
      <CompleteRoundModal />
      <MoreOptionsModal
        show={showMoreOptionsModal}
        setShow={setShowMoreOptionsModal}
        setShowDeleteBracketModal={setShowDeleteBracketModal}
        setShowEditBracketModal={setShowEditBracketModal}
        setShowLockLiveTournamentModal={setShowLockLiveTournamentModal}
        setShowSetTournamentFeeModal={setShowSetTournamentFeeModal}
        setShowShareBracketModal={setShowShareBracketModal}
        bracketId={bracketId}
        bracketTitle={bracketTitle}
        bracketMonth={bracketMonth}
        setBracketMonth={setBracketMonth}
        bracketYear={bracketYear}
        setBracketYear={setBracketYear}
        bracketFee={bracketFee}
        setBracketFee={setBracketFee}
        playBracketUrl={playBracketUrl}
        setPlayBracketUrl={setPlayBracketUrl}
        copyBracketUrl={copyBracketUrl}
        setCopyBracketUrl={setCopyBracketUrl}
        mostPopularPicksUrl={mostPopularPicksUrl}
        setMostPopularPicksUrl={setMostPopularPicksUrl}
      />
    </>
  )
}
